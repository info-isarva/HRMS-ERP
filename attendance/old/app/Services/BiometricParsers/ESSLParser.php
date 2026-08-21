<?php

namespace App\Services\BiometricParsers;

use Carbon\Carbon;

/**
 * eSSL Biometric Device Parser
 * Format: CSV files with specific column order
 * Columns: User ID, Name, Date, Time, Status, Device
 */
class ESSLParser implements BiometricParserInterface
{
    public function getFormatName(): string
    {
        return 'eSSL';
    }

    public function getSupportedExtensions(): array
    {
        return ['csv', 'txt'];
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

        $firstLine = fgetcsv($handle);
        fclose($handle);

        // Check if it's CSV and has at least 4 columns
        return is_array($firstLine) && count($firstLine) >= 4;
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
        $hasHeader = false;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (empty($row) || count($row) < 3) {
                continue;
            }

            // Skip header row if detected
            if ($lineNumber === 1) {
                if ($this->isHeaderRow($row)) {
                    $hasHeader = true;
                    continue;
                }
            }

            try {
                // eSSL Format: User ID, Name, Date, Time, Status, Device
                // Example: 1001, "John Doe", "02/12/2025", "09:15:00", "In", "Device1"
                
                $employeeId = trim($row[0]);
                $name = isset($row[1]) ? trim($row[1]) : '';
                $dateStr = isset($row[2]) ? trim($row[2]) : '';
                $timeStr = isset($row[3]) ? trim($row[3]) : '';
                $status = isset($row[4]) ? trim($row[4]) : '';
                $device = isset($row[5]) ? trim($row[5]) : '';

                // Parse date
                $date = $this->parseDate($dateStr);
                if (!$date) {
                    continue;
                }

                // Parse time
                $time = $this->parseTime($timeStr);
                if (!$time) {
                    continue;
                }

                $dateStr = $date->format('Y-m-d');
                $timeFormatted = $time->format('H:i:s');

                // Combine date and time for sorting
                $datetime = Carbon::parse($dateStr . ' ' . $timeFormatted);

                // Group by employee and date
                $key = $employeeId . '|' . $dateStr;

                if (!isset($groupedRecords[$key])) {
                    $groupedRecords[$key] = [
                        'employee_id' => $employeeId,
                        'date' => $dateStr,
                        'employee_name' => $name,
                        'check_in' => null,
                        'check_out' => null,
                        'device_id' => $device,
                        'raw_punches' => []
                    ];
                }

                $groupedRecords[$key]['raw_punches'][] = [
                    'time' => $timeFormatted,
                    'status' => $status,
                    'datetime' => $datetime
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

    private function isHeaderRow(array $row): bool
    {
        $headerKeywords = ['user', 'employee', 'id', 'name', 'date', 'time', 'status'];
        $matchCount = 0;

        foreach ($row as $cell) {
            $cellLower = strtolower(trim($cell));
            foreach ($headerKeywords as $keyword) {
                if (strpos($cellLower, $keyword) !== false) {
                    $matchCount++;
                    break;
                }
            }
        }

        return $matchCount >= 2;
    }

    private function parseDate(string $dateStr): ?Carbon
    {
        try {
            $formats = [
                'd/m/Y',
                'm/d/Y',
                'Y-m-d',
                'd-m-Y',
                'm-d-Y',
                'Y/m/d',
                'd.m.Y',
                'Y.m.d'
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateStr);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($dateStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseTime(string $timeStr): ?Carbon
    {
        try {
            $formats = [
                'H:i:s',
                'H:i',
                'h:i:s A',
                'h:i A',
                'g:i:s A',
                'g:i A'
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $timeStr);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($timeStr);
        } catch (\Exception $e) {
            return null;
        }
    }
}
