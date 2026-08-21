<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    /**
     * Create a downloadable mysqldump backup of the configured database.
     * Requires mysqldump available on the server PATH.
     */
    public function backup(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->crm_role_type, [0, 1])) {
            abort(403, 'Unauthorized');
        }

        $connection = config('database.default');
        $db = config("database.connections.mysql", []);

        $database = $db['database'] ?? env('DB_DATABASE');
        $username = $db['username'] ?? env('DB_USERNAME');
        $password = $db['password'] ?? env('DB_PASSWORD');
        $host     = $db['host'] ?? env('DB_HOST', '127.0.0.1');
        $port     = $db['port'] ?? env('DB_PORT', 3306);

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $fileName = 'db_backup_' . date('Ymd_His') . '.sql';
        $fullPath = $backupDir . DIRECTORY_SEPARATOR . $fileName;
        $gzPath = $fullPath . '.gz';

        // Escape values safely
        $escapedDb   = escapeshellarg($database);
        $escapedHost = escapeshellarg($host);
        $escapedUser = escapeshellarg($username);
        $escapedPort = escapeshellarg($port);
        $escapedOut  = escapeshellarg($fullPath);

        // Password handling
        $passwordPart = $password ? sprintf('--password=%s', escapeshellarg($password)) : '';

        // Attempt to find mysqldump executable. On Windows 'mysqldump' may not be in PATH.
        $mysqldumpPath = null;
        // Try 'where' (Windows) or 'which' (Unix)
        try {
            $whichOutput = null;
            $whichResult = null;
            if (stripos(PHP_OS, 'WIN') === 0) {
                @exec('where mysqldump 2>NUL', $whichOutput, $whichResult);
            } else {
                @exec('which mysqldump 2>/dev/null', $whichOutput, $whichResult);
            }
            if (!empty($whichOutput) && is_array($whichOutput)) {
                $mysqldumpPath = trim($whichOutput[0]);
            }
        } catch (\Exception $e) {
            $mysqldumpPath = null;
        }
        // If not found, try common Windows/MAMP/XAMPP locations
        if (!$mysqldumpPath) {
            $possible = [];
            if (stripos(PHP_OS, 'WIN') === 0) {
                $possible = [
                    'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                    'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
                    'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                    'C:\\wamp64\\bin\\mysql\\mysql8.0.0\\bin\\mysqldump.exe',
                ];
            } else {
                $possible = ['/usr/bin/mysqldump', '/usr/local/bin/mysqldump'];
            }
            foreach ($possible as $p) {
                if (file_exists($p) && is_executable($p)) {
                    $mysqldumpPath = $p;
                    break;
                }
            }
        }

        // Build command using found path or plain 'mysqldump' (will fail if not available)
        $dumpBin = $mysqldumpPath ? escapeshellarg($mysqldumpPath) : 'mysqldump';
        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s %s %s > %s',
            $dumpBin,
            $escapedHost,
            $escapedPort,
            $escapedUser,
            $passwordPart,
            $escapedDb,
            $escapedOut
        );

        try {
            $output = [];
            $result = null;
            exec($cmd . ' 2>&1', $output, $result);

            if ($result !== 0 || !file_exists($fullPath)) {
                // mysqldump not available or failed — fall back to PHP dump
                $debug = implode("\n", array_slice($output ?? [], 0, 50));
                // Attempt PHP fallback to generate SQL and compress
                try {
                    $this->generatePhpSqlFile($database, $fullPath);
                    // compress
                    $gzPath = $fullPath . '.gz';
                    $fpIn = fopen($fullPath, 'rb');
                    if ($fpIn !== false) {
                        $gz = gzopen($gzPath, 'wb9');
                        while (!feof($fpIn)) {
                            $chunk = fread($fpIn, 1024 * 1024);
                            gzwrite($gz, $chunk);
                        }
                        gzclose($gz);
                        fclose($fpIn);
                        @unlink($fullPath);
                        if (file_exists($gzPath) && filesize($gzPath) > 0) {
                            return response()->download($gzPath)->deleteFileAfterSend(true);
                        }
                    }
                    return redirect()->back()->with('error', 'Backup fallback failed to produce compressed dump. Debug: ' . $debug);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Database backup failed and PHP fallback failed: ' . $e->getMessage() . '\nOutput: ' . $debug);
                }
            }

            // Compress the SQL file to .gz using PHP so it works on Windows without gzip in PATH
            $fpIn = fopen($fullPath, 'rb');
            if ($fpIn === false) {
                return redirect()->back()->with('error', 'Backup created but failed to open SQL file for compression.');
            }
            $gz = gzopen($gzPath, 'wb9');
            if ($gz === false) {
                fclose($fpIn);
                return redirect()->back()->with('error', 'Failed to create compressed file.');
            }
            while (!feof($fpIn)) {
                $chunk = fread($fpIn, 1024 * 1024);
                gzwrite($gz, $chunk);
            }
            gzclose($gz);
            fclose($fpIn);
            // Remove the uncompressed SQL to save space
            @unlink($fullPath);

            if (file_exists($gzPath) && filesize($gzPath) > 0) {
                return response()->download($gzPath)->deleteFileAfterSend(true);
            }

            return redirect()->back()->with('error', 'Backup created but compressed file is missing.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Create a stored compressed backup in storage/app/backups and return the path.
     * This does not stream the file to the user.
     * Throws exception on failure.
     */
    public function createStoredBackup()
    {
        $db = config("database.connections.mysql", []);

        $database = $db['database'] ?? env('DB_DATABASE');

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $fileName = 'db_backup_' . date('Ymd_His') . '.sql';
        $fullPath = $backupDir . DIRECTORY_SEPARATOR . $fileName;
        $gzPath = $fullPath . '.gz';

        // Try mysqldump first similar to backup()
        $username = $db['username'] ?? env('DB_USERNAME');
        $password = $db['password'] ?? env('DB_PASSWORD');
        $host     = $db['host'] ?? env('DB_HOST', '127.0.0.1');
        $port     = $db['port'] ?? env('DB_PORT', 3306);

        $escapedDb   = escapeshellarg($database);
        $escapedHost = escapeshellarg($host);
        $escapedUser = escapeshellarg($username);
        $escapedPort = escapeshellarg($port);
        $escapedOut  = escapeshellarg($fullPath);
        $passwordPart = $password ? sprintf('--password=%s', escapeshellarg($password)) : '';

        $mysqldumpPath = null;
        try {
            $whichOutput = null;
            $whichResult = null;
            if (stripos(PHP_OS, 'WIN') === 0) {
                @exec('where mysqldump 2>NUL', $whichOutput, $whichResult);
            } else {
                @exec('which mysqldump 2>/dev/null', $whichOutput, $whichResult);
            }
            if (!empty($whichOutput) && is_array($whichOutput)) {
                $mysqldumpPath = trim($whichOutput[0]);
            }
        } catch (\Exception $e) {
            $mysqldumpPath = null;
        }

        if (!$mysqldumpPath) {
            $possible = [];
            if (stripos(PHP_OS, 'WIN') === 0) {
                $possible = [
                    'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                    'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                ];
            } else {
                $possible = ['/usr/bin/mysqldump', '/usr/local/bin/mysqldump'];
            }
            foreach ($possible as $p) {
                if (file_exists($p) && is_executable($p)) {
                    $mysqldumpPath = $p;
                    break;
                }
            }
        }

        $dumpBin = $mysqldumpPath ? escapeshellarg($mysqldumpPath) : 'mysqldump';
        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s %s %s > %s',
            $dumpBin,
            $escapedHost,
            $escapedPort,
            $escapedUser,
            $passwordPart,
            $escapedDb,
            $escapedOut
        );

        $output = [];
        $result = null;
        @exec($cmd . ' 2>&1', $output, $result);

        if ($result !== 0 || !file_exists($fullPath)) {
            // Fallback to PHP generator
            $this->generatePhpSqlFile($database, $fullPath);
        }

        // Compress to gz
        $fpIn = fopen($fullPath, 'rb');
        if ($fpIn === false) {
            throw new \Exception('Failed to open generated SQL file for compression');
        }
        $gz = gzopen($gzPath, 'wb9');
        if ($gz === false) {
            fclose($fpIn);
            throw new \Exception('Failed to create gz file');
        }
        while (!feof($fpIn)) {
            $chunk = fread($fpIn, 1024 * 1024);
            gzwrite($gz, $chunk);
        }
        gzclose($gz);
        fclose($fpIn);
        @unlink($fullPath);

        if (!file_exists($gzPath) || filesize($gzPath) === 0) {
            throw new \Exception('Compressed backup missing or empty');
        }

        return $gzPath;
    }

    /**
     * Generate an SQL file using PHP (non-streaming) for fallback.
     */
    protected function generatePhpSqlFile($database, $outPath)
    {
        $pdo = \DB::connection()->getPdo();
        $fp = fopen($outPath, 'wb');
        if ($fp === false) throw new \Exception('Unable to create SQL file');
        fwrite($fp, "-- PHP generated dump for database: {$database}\n");
        fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($fp, "CREATE DATABASE IF NOT EXISTS `{$database}`;\n");
        fwrite($fp, "USE `{$database}`;\n\n");

        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $table = $row[0];
            fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");
            $row2 = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $create = $row2['Create Table'] ?? $row2['Create View'] ?? null;
            if ($create) {
                fwrite($fp, $create . ";\n\n");
            }
            $colStmt = $pdo->query("DESCRIBE `{$table}`");
            $cols = [];
            while ($c = $colStmt->fetch(\PDO::FETCH_ASSOC)) {
                $cols[] = "`" . $c['Field'] . "`";
            }
            if (empty($cols)) continue;
            $colList = implode(', ', $cols);
            $offset = 0;
            $limit = 500;
            do {
                $dataStmt = $pdo->query("SELECT * FROM `{$table}` LIMIT {$offset}, {$limit}");
                $rows = $dataStmt->fetchAll(\PDO::FETCH_NUM);
                if (empty($rows)) break;
                foreach ($rows as $r) {
                    $vals = array_map(function ($v) {
                        if ($v === null) return 'NULL';
                        return "'" . str_replace("'", "\\'", (string)$v) . "'";
                    }, $r);
                    fwrite($fp, "INSERT INTO `{$table}` ({$colList}) VALUES (" . implode(', ', $vals) . ");\n");
                }
                $offset += $limit;
            } while (count($rows) === $limit);
            fwrite($fp, "\n");
        }
        fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fp);
    }
}
