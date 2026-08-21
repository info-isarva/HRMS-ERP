<?php

namespace App\Services;

use App\Models\TimeStationLog;
use App\Models\TimeStationMapping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TimeStationService
{
    private $apiKey;
    private $baseUrl = 'https://api.mytimestation.com/v1.2';

    public function __construct()
    {
        // Ideally from config, but using the provided key for Phase 1
        $this->apiKey = '319d0q7p62jnwvme0ox5kyzeg3r81lrq'; 
    }


    public function fetchActivities($startDate, $endDate)
    {
        // Strictly use parameters matching working Postman request
        // Added exportformat=csv as our system expects to parse CSV data
        $url = "{$this->baseUrl}/reports/EmployeeActivity?report_startdate={$startDate}&report_enddate={$endDate}&exportformat=csv";
        
        Log::info('TimeStation Fetching URL', ['url' => $url]);

        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Authorization: Basic " . base64_encode("{$this->apiKey}:") . "\r\n" .
                                "Accept: */*\r\n" .
                                "User-Agent: PHP/Laravel\r\n",
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                Log::error('TimeStation Connection Error: file_get_contents failed');
                return [];
            }

            Log::debug('TimeStation API Response Received', ['length' => strlen($response)]);

            // Parse CSV
            return $this->parseCsv($response);
        } catch (\Exception $e) {
            Log::error('TimeStation Connection Error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function parseCsv($csvData)
    {
        $lines = explode("\n", trim($csvData));
        $header = str_getcsv(array_shift($lines));
        
        // Normalize header keys: "Employee ID" -> "employee_id"
        $headerMap = [];
        foreach ($header as $key => $col) {
            $slug = strtolower(str_replace(' ', '_', $col));
            $headerMap[$key] = $slug;
        }

        $results = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $row = str_getcsv($line);
            $item = [];
            foreach ($row as $key => $value) {
                if (isset($headerMap[$key])) {
                    $item[$headerMap[$key]] = $value;
                }
            }
            
            // Standardize generic fields for syncLogs
            // CSV columns: Date, Employee ID, Name, Department, Device, Time, Activity, etc.
            if (isset($item['employee_id'])) {
                $results[] = $item;
            }
        }

        return $results;
    }

    /**
     * Process and store raw activities into TimeStationLog (Staging).
     */
    public function syncLogs($activities)
    {
        $count = 0;
        foreach ($activities as $activity) {
            $tsUserId = $activity['employee_id'] ?? null;
            if (!$tsUserId) continue;

            $uniqueId = $this->generateActivityId($activity);
            
            // Check if ignored
            $mapping = TimeStationMapping::where('ts_user_id', $tsUserId)->first();
            
            if ($mapping && $mapping->is_ignored) {
                continue;
            }

            $activityType = $activity['activity'] ?? 'Unknown';
            // Normalize activity type
            if (stripos($activityType, 'In') !== false) $activityType = 'CheckIn';
            if (stripos($activityType, 'Out') !== false) $activityType = 'CheckOut';

            // Device ID might be 'Device' in CSV
            $deviceId = $activity['device'] ?? $activity['device_id'] ?? null;
            
            // Timestamp combination
            $date = $activity['date'] ?? null;
            $time = $activity['time'] ?? '00:00:00';
            $timestamp = $date ? Carbon::parse("$date $time") : now();

            TimeStationLog::updateOrCreate(
                ['ts_activity_id' => $uniqueId],
                [
                    'ts_user_id' => $tsUserId,
                    'employee_payroll_id' => $mapping ? $mapping->employee_payroll_id : null,
                    'timestamp' => $timestamp,
                    'activity_type' => $activityType,
                    'device_id' => $deviceId,
                    'gps_location' => ($activity['latitude'] ?? '') . ',' . ($activity['longitude'] ?? ''),
                    'raw_response' => $activity,
                    'sync_status' => $mapping ? 'mapped' : 'unmapped',
                ]
            );
            $count++;
        }
        return $count;
    }

    private function generateActivityId($activity)
    {
        // CSV doesn't have ID, so hash content
        $str = ($activity['employee_id'] ?? '') . 
               ($activity['date'] ?? '') . 
               ($activity['time'] ?? '') . 
               ($activity['activity'] ?? '');
        return md5($str);
    }

    /**
     * Get list of unique users from Logs who are not yet mapped.
     */
    public function getUnmappedUsers()
    {
        return TimeStationLog::whereNull('employee_payroll_id')
            ->where('sync_status', '!=', 'ignored')
            ->select('ts_user_id')
            ->distinct()
            ->get()
            ->map(function ($log) {
                // Try to find the name from the latest log for this user
                $latest = TimeStationLog::where('ts_user_id', $log->ts_user_id)->latest('timestamp')->first();
                return [
                    'ts_user_id' => $log->ts_user_id,
                    'name' => $latest->raw_response['name'] ?? $latest->raw_response['employee_name'] ?? 'Unknown',
                    'last_seen' => $latest->timestamp->format('Y-m-d H:i')
                ];
            });
    }
}
