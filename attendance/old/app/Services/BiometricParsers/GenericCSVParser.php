<?php

namespace App\Services\BiometricParsers;

use Carbon\Carbon;

/**
 * Generic CSV Parser for biometric attendance
 * Flexible parser that can handle various CSV formats with column mapping
 */
class GenericCSVParser implements BiometricParserInterface
{
    public function getFormatName(): string
    {
        return 'Generic CSV';
    }

    public function getSupportedExtensions(): array
    {
        return ['csv'];
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

        return is_array($firstLine) && count($firstLine) >= 2;
    }

    public function parse(string $filePath): array
    {
        $records = [];
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \Exception("Cannot open file: {$filePath}");
        }

        $lineNumber = 0;
        $headers = [];
        $columnMap = [];
        $groupedRecords = [];

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (empty($row)) {
                continue;
            }

            // First row - detect headers
            if ($lineNumber === 1) {
                $headers = array_map(function($h) {
                    return strtolower(trim($h));
                }, $row);

                // Map columns to expected fields
                $columnMap = $this->mapColumns($headers);

                // If no valid mapping found, treat first row as data
                if (empty($columnMap['employee_id']) && empty($columnMap['date'])) {
                    // Process first row as data
                    $this->processRow($row, range(0, count($row) - 1), $groupedRecords);
                }
                continue;
            }

            // Process data rows
            if (!empty($columnMap)) {
                $this->processRowWithMapping($row, $columnMap, $groupedRecords);
            } else {
                // No header, use default column positions
                $this->processRow($row, range(0, count($row) - 1), $groupedRecords);
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

    private function mapColumns(array $headers): array
    {
        $map = [
            'employee_id' => null,
            'date' => null,
            'time' => null,
            'datetime' => null,
            'status' => null,
        ];

        foreach ($headers as $index => $header) {
            $header = strtolower(trim($header));

            // Employee ID variations
            if (preg_match('/emp.*id|employee.*id|payroll.*id|user.*id|enroll|badge/i', $header)) {
                $map['employee_id'] = $index;
            }

            // Date variations
            if (preg_match('/^date$|attendance.*date|punch.*date|work.*date/i', $header) && !$map['date']) {
                $map['date'] = $index;
            }

            // Time variations
            if (preg_match('/^time$|punch.*time|attendance.*time/i', $header) && !$map['time']) {
                $map['time'] = $index;
            }

            // DateTime combined
            if (preg_match('/datetime|date.*time|timestamp/i', $header)) {
                $map['datetime'] = $index;
            }

            // Status
            if (preg_match('/status|in.*out|type/i', $header)) {
                $map['status'] = $index;
            }
        }

        return $map;
    }

    private function processRowWithMapping(array $row, array $columnMap, array &$groupedRecords): void
    {
        try {
            // Get employee ID
            $employeeId = null;
            if (isset($columnMap['employee_id']) && isset($row[$columnMap['employee_id']])) {
                $employeeId = trim($row[$columnMap['employee_id']]);
            }

            if (!$employeeId) {
                return;
            }

            // Get date and time
            $datetime = null;

            // Try datetime column first
            if (isset($columnMap['datetime']) && isset($row[$columnMap['datetime']])) {
                $datetime = $this->parseDateTime(trim($row[$columnMap['datetime']]));
            }

            // If no datetime, try separate date and time
            if (!$datetime) {
                $dateStr = isset($columnMap['date']) && isset($row[$columnMap['date']]) 
                    ? trim($row[$columnMap['date']]) 
                    : null;
                    
                $timeStr = isset($columnMap['time']) && isset($row[$columnMap['time']]) 
                    ? trim($row[$columnMap['time']]) 
                    : null;

                if ($dateStr) {
                    if ($timeStr) {
                        $datetime = $this->parseDateTime($dateStr . ' ' . $timeStr);
                    } else {
                        $datetime = $this->parseDateTime($dateStr);
                    }
                }
            }

            if (!$datetime) {
                return;
            }

            $date = $datetime->format('Y-m-d');
            $time = $datetime->format('H:i:s');

            // Group by employee and date
            $key = $employeeId . '|' . $date;

            if (!isset($groupedRecords[$key])) {
                $groupedRecords[$key] = [
                    'employee_id' => $employeeId,
                    'date' => $date,
                    'check_in' => null,
                    'check_out' => null,
                    'raw_punches' => []
                ];
            }

            $groupedRecords[$key]['raw_punches'][] = [
                'time' => $time,
                'datetime' => $datetime
            ];

        } catch (\Exception $e) {
            // Skip invalid rows
        }
    }

    private function processRow(array $row, array $indices, array &$groupedRecords): void
    {
        try {
            // Assume: Column 0 = Employee ID, Column 1 = Date or DateTime, Column 2 = Time (optional)
            if (count($row) < 2) {
                return;
            }

            $employeeId = trim($row[0]);
            $dateTimeStr = trim($row[1]);
            $timeStr = isset($row[2]) ? trim($row[2]) : null;

            // Parse datetime
            $datetime = null;

            // Try as datetime first
            $datetime = $this->parseDateTime($dateTimeStr);

            // If time column exists and datetime is only date, combine them
            if ($datetime && $timeStr) {
                $datePart = $datetime->format('Y-m-d');
                $datetime = $this->parseDateTime($datePart . ' ' . $timeStr);
            }

            if (!$datetime) {
                return;
            }

            $date = $datetime->format('Y-m-d');
            $time = $datetime->format('H:i:s');

            // Group by employee and date
            $key = $employeeId . '|' . $date;

            if (!isset($groupedRecords[$key])) {
                $groupedRecords[$key] = [
                    'employee_id' => $employeeId,
                    'date' => $date,
                    'check_in' => null,
                    'check_out' => null,
                    'raw_punches' => []
                ];
            }

            $groupedRecords[$key]['raw_punches'][] = [
                'time' => $time,
                'datetime' => $datetime
            ];

        } catch (\Exception $e) {
            // Skip invalid rows
        }
    }

    private function parseDateTime(string $dateTimeStr): ?Carbon
    {
        try {
            $formats = [
                'Y-m-d H:i:s',
                'Y-m-d H:i',
                'd/m/Y H:i:s',
                'm/d/Y H:i:s',
                'd-m-Y H:i:s',
                'Y/m/d H:i:s',
                'd/m/Y H:i',
                'm/d/Y H:i',
                'Y-m-d',
                'd/m/Y',
                'm/d/Y',
                'd-m-Y',
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
