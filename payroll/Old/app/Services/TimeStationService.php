<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TimeStationService
{
    private $apiKey;
    private $baseUrl = 'https://api.mytimestation.com/v1.2';

    public function __construct()
    {
        // Using provided key
        $this->apiKey = '319d0q7p62jnwvme0ox5kyzeg3r81lrq'; 
    }

    /**
     * Create a new employee in TimeStation.
     * 
     * @param array $data Employee Data
     * @return array|false Response data or false on failure
     */
    public function createEmployee($data)
    {
        $url = "{$this->baseUrl}/employees";
        
        $payload = [
            'name' => $data['name'],
            'department_name' => $data['department_name'] ?? 'General',
            'custom_employee_id' => $data['custom_employee_id'],
            'title' => $data['title'] ?? 'Employee',
            'email' => $data['email'] ?? '',
        ];

        if (isset($data['hourly_rate'])) {
            $payload['hourly_rate'] = $data['hourly_rate'];
        }

        if (isset($data['pin'])) {
            $payload['pin'] = $data['pin'];
        }

        $customFields = [];
        if (!empty($data['phone'])) {
            $customFields['Phone Number'] = $data['phone'];
        }
        if (!empty($data['start_date'])) {
            try {
                // Ensure correct format mm/dd/yyyy if passed as date string
                $customFields['Start Date'] = \Carbon\Carbon::parse($data['start_date'])->format('m/d/Y');
            } catch (\Exception $e) {
                // Keep as is if parsing fails
                $customFields['Start Date'] = $data['start_date'];
            }
        }

        if (!empty($customFields)) {
            $payload['custom_fields'] = $customFields;
        }

        try {
            // Using Basic Auth: username is API key, password is empty
            $response = Http::withBasicAuth($this->apiKey, '')
                ->asForm()
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info('TimeStation Employee Created', ['data' => $payload, 'response' => $response->json()]);
                return $response->json();
            } else {
                $errorData = $response->json();
                $errorMsg = $errorData['error']['error_text'] ?? 'Unknown TimeStation Error';
                Log::error('TimeStation Creation Failed', ['status' => $response->status(), 'body' => $response->body()]);
                throw new \Exception("TimeStation Error: " . strip_tags($errorMsg));
            }
        } catch (\Exception $e) {
            Log::error('TimeStation Connection Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
