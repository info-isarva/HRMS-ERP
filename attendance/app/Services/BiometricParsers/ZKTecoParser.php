<?php

namespace App\Services\BiometricParsers;

use Carbon\Carbon;

/**
 * ZKTeco Biometric Device Parser
 * Format: DAT or ATT files with tab-separated values
 * Columns: EmployeeID\tDateTime\tStatus\tDeviceID
 */
class ZKTecoParser implements BiometricParserInterface
{
    public function getFormatName(): string
    {
        return 'ZKTeco';
    }

    public function getSupportedExtensions(): array
    {
        return ['dat', 'att', 'txt'];
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

        $firstLine = fgets($handle);
        fclose($handle);

        // Check if it's tab-separated and has expected pattern
        return (substr_count($firstLine, "\t") >= 1) && preg_match('/^\d+\t/', $firstLine);
    }

    public function parse(string $filePath): array
    {
        $records = [];
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \Exception("Cannot open file: {$filePath}");
        }

        $lineNumber = 0;
        $groupedRecords = []; // Group by employee and date

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            try {
                // ZKTeco Format: EmployeeID	DateTime	Status	DeviceID
                // Example: 1001	2025-12-02 09:15:00	0	1
                $parts = explode("\t", $line);

                if (count($parts) < 2) {
                    continue;
                }

                $employeeId = trim($parts[0]);
                $dateTimeStr = trim($parts[1]);
                $status = isset($parts[2]) ? trim($parts[2]) : '0';
                $deviceId = isset($parts[3]) ? trim($parts[3]) : null;

                // Parse datetime
                $dateTime = $this->parseDateTime($dateTimeStr);
                if (!$dateTime) {
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
                        'device_id' => $deviceId,
                        'raw_punches' => []
                    ];
                }

                $groupedRecords[$key]['raw_punches'][] = [
                    'time' => $time,
                    'status' => $status,
                    'datetime' => $dateTime
                ];

            } catch (\Exception $e) {
                // Skip invalid lines
                continue;
            }
        }

        fclose($handle);

        // Process grouped records to determine check-in and check-out
        foreach ($groupedRecords as $record) {
            if (empty($record['raw_punches'])) {
                continue;
            }

            // Sort punches by time
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

    private function parseDateTime(string $dateTimeStr): ?Carbon
    {
        try {
            // Try common formats
            $formats = [
                'Y-m-d H:i:s',
                'd/m/Y H:i:s',
                'm/d/Y H:i:s',
                'Y/m/d H:i:s',
                'd-m-Y H:i:s',
                'm-d-Y H:i:s',
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateTimeStr);
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Try general parse
            return Carbon::parse($dateTimeStr);
        } catch (\Exception $e) {
            return null;
        }
    }
}
