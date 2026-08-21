<?php

namespace App\Services\BiometricParsers;

use Carbon\Carbon;

/**
 * Realtime Biometric Device Parser
 * Format: Fixed-width text files or space-separated
 * Format: AC No.  Enroll No.   Name    Auto Assign   InOut  VerifyCode  Door/Device  Time
 */
class RealtimeParser implements BiometricParserInterface
{
    public function getFormatName(): string
    {
        return 'Realtime';
    }

    public function getSupportedExtensions(): array
    {
        return ['txt', 'log'];
    }

    public function validate(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return false;
        }

        $content = '';
        for ($i = 0; $i < 3; $i++) {
            $line = fgets($handle);
            if ($line === false) break;
            $content .= $line;
        }
        fclose($handle);

        // Check for Realtime patterns - spaces and typical columns
        return (preg_match('/\d+\s+\d+\s+.+\s+\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}/', $content) > 0);
    }

    public function parse(string $filePath): array
    {
        $records = [];
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \Exception("Cannot open file: {$filePath}");
        }

        $lineNumber = 0;
        $groupedRecords = [];

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            // Skip header lines
            if ($lineNumber <= 2 && $this->isHeaderLine($line)) {
                continue;
            }

            try {
                // Realtime Format (space or tab separated):
                // AC No.  Enroll No.  Name  Auto Assign  InOut  VerifyCode  Door/Device  DateTime
                // Example: 1  1001  "John Doe"  1  0  1  Main Gate  2025-12-02 09:15:00
                
                // Split by multiple spaces or tabs
                $parts = preg_split('/\s{2,}|\t+/', $line);
                
                // Filter empty parts
                $parts = array_filter(array_map('trim', $parts));
                $parts = array_values($parts);

                if (count($parts) < 3) {
                    continue;
                }

                // Try to identify employee ID and datetime from the parts
                $employeeId = null;
                $dateTime = null;
                $inOut = null;
                $device = null;

                // Look for employee ID (second numeric column - "Enroll No." not "AC No.")
                // Skip the first numeric value as it's usually AC No. (sequential number)
                $numericFound = 0;
                foreach ($parts as $idx => $part) {
                    if (is_numeric($part) && strlen($part) >= 1 && strlen($part) <= 10) {
                        $numericFound++;
                        // Take the second numeric value as employee ID (Enroll No.)
                        if ($numericFound == 2) {
                            // Convert to integer to remove leading zeros
                            $employeeId = (string)(int)$part;
                            break;
                        }
                    }
                }
                
                // If only one numeric column found, use it as employee ID
                if (!$employeeId && $numericFound == 1) {
                    foreach ($parts as $idx => $part) {
                        if (is_numeric($part) && strlen($part) >= 1 && strlen($part) <= 10) {
                            $employeeId = (string)(int)$part;
                            break;
                        }
                    }
                }

                // Look for datetime (contains date and time pattern)
                foreach ($parts as $part) {
                    if (preg_match('/\d{4}-\d{2}-\d{2}/', $part)) {
                        // Found date, check next parts for time
                        $dateTime = $this->parseDateTime($part);
                        break;
                    }
                }

                // If datetime not found in combined format, try separate date/time columns
                if (!$dateTime) {
                    // Look for separate date and time
                    $dateStr = null;
                    $timeStr = null;

                    foreach ($parts as $part) {
                        if (preg_match('/^\d{2}[\/\-]\d{2}[\/\-]\d{4}$|^\d{4}[\/\-]\d{2}[\/\-]\d{2}$/', $part)) {
                            $dateStr = $part;
                        }
                        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $part)) {
                            $timeStr = $part;
                        }
                    }

                    if ($dateStr && $timeStr) {
                        $dateTime = $this->parseDateTime($dateStr . ' ' . $timeStr);
                    }
                }

                if (!$employeeId || !$dateTime) {
                    continue;
                }

                $date = $dateTime->format('Y-m-d');
                $time = $dateTime->format('H:i:s');

                // Group by employee and date
                $key = $employeeId . '|' . $date;

                if (!isset($groupedRecords[$key])) {
                    $groupedRecords[$key] = [
                        'employee_id' => $employeeId,
                        'date' => $date,
                        'check_in' => null,
                        'check_out' => null,
                        'device_id' => $device,
                        'raw_punches' => []
                    ];
                }

                $groupedRecords[$key]['raw_punches'][] = [
                    'time' => $time,
                    'datetime' => $dateTime
                ];

            } catch (\Exception $e) {
                continue;
            }
        }

        fclose($handle);

        // Process grouped records
        foreach ($groupedRecords as $record) {
            if (empty($record['raw_punches'])) {
                continue;
            }

            // Sort punches by datetime
            usort($record['raw_punches'], function($a, $b) {
                return $a['datetime']->timestamp <=> $b['datetime']->timestamp;
            });

            // First punch is check-in, last is check-out
            $record['check_in'] = $record['raw_punches'][0]['time'];
            if (count($record['raw_punches']) > 1) {
                $record['check_out'] = end($record['raw_punches'])['time'];
            }

            unset($record['raw_punches']);
            $records[] = $record;
        }

        return $records;
    }

    private function isHeaderLine(string $line): bool
    {
        $headerKeywords = ['AC No', 'Enroll', 'Name', 'Time', 'Device', 'InOut', 'Verify'];
        $lineLower = strtolower($line);

        $matchCount = 0;
        foreach ($headerKeywords as $keyword) {
            if (stripos($line, $keyword) !== false) {
                $matchCount++;
            }
        }

        return $matchCount >= 2;
    }

    private function parseDateTime(string $dateTimeStr): ?Carbon
    {
        try {
            $formats = [
                'Y-m-d H:i:s',
                'd/m/Y H:i:s',
                'm/d/Y H:i:s',
                'Y/m/d H:i:s',
                'd-m-Y H:i:s',
                'm-d-Y H:i:s',
                'Y-m-d H:i',
                'd/m/Y H:i',
                'm/d/Y H:i',
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateTimeStr);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($dateTimeStr);
        } catch (\Exception $e) {
            return null;
        }
    }
}
