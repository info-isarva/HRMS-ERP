<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySettings;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Get comprehensive company settings for attendance system integration
     * This is the main API endpoint similar to employees API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompanySettings(Request $request): JsonResponse
    {
        try {
            $companySettings = CompanySettings::first();
            
            if (!$companySettings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company settings not found',
                    'data' => null,
                    'meta' => [
                        'total' => 0,
                        'timestamp' => now()->toISOString(),
                        'version' => '1.0'
                    ]
                ], 404);
            }

            // Generate full URLs for logo and favicon
            $baseUrl = url('/');
            
            // Comprehensive company data structure
            $data = [
                // Basic Info
                'id' => $companySettings->id,
                'company_name' => $companySettings->company_name,
                'contact_person' => $companySettings->contact_person,
                'email' => $companySettings->email,
                'phone_number' => $companySettings->phone_number,
                'mobile_number' => $companySettings->mobile_number,
                'fax' => $companySettings->fax,
                'website_url' => $companySettings->website_url,
                
                // Address Information
                'address' => [
                    'street' => $companySettings->address,
                    'city' => $companySettings->city,
                    'state_province' => $companySettings->state_province,
                    'country' => $companySettings->country,
                    'postal_code' => $companySettings->postal_code,
                    'full_address' => trim($companySettings->address . ', ' . 
                                          $companySettings->city . ', ' . 
                                          $companySettings->state_province . ', ' . 
                                          $companySettings->country . ' ' . 
                                          $companySettings->postal_code, ', ')
                ],
                
                // Assets with both URLs and paths
                'assets' => [
                    'logo' => [
                        'url' => $companySettings->logo_url,
                        'path' => $companySettings->logo_image,
                        'exists' => !empty($companySettings->logo_image)
                    ],
                    'favicon' => [
                        'url' => $companySettings->favicon_url,
                        'path' => $companySettings->favicon,
                        'exists' => !empty($companySettings->favicon)
                    ]
                ],
                
                // Quick access URLs (for backward compatibility)
                'logo_url' => $companySettings->logo_url,
                'favicon_url' => $companySettings->favicon_url,
                'logo_path' => $companySettings->logo_image,
                'favicon_path' => $companySettings->favicon,
                
                // System Information
                'system' => [
                    'base_url' => $baseUrl,
                    'source_system' => 'payroll',
                    'api_version' => '1.0',
                    'last_updated' => $companySettings->updated_at,
                    'created' => $companySettings->created_at
                ],
                
                // Contact Information (structured)
                'contact' => [
                    'primary_email' => $companySettings->email,
                    'primary_phone' => $companySettings->phone_number,
                    'mobile_phone' => $companySettings->mobile_number,
                    'fax_number' => $companySettings->fax,
                    'contact_person' => $companySettings->contact_person,
                    'website' => $companySettings->website_url
                ],
                
                // Timestamps
                'created_at' => $companySettings->created_at,
                'updated_at' => $companySettings->updated_at,
                'last_sync' => now()->toISOString()
            ];

            // Check if specific data is requested via query parameters
            $fields = $request->get('fields');
            if ($fields) {
                $requestedFields = explode(',', $fields);
                $filteredData = [];
                foreach ($requestedFields as $field) {
                    $field = trim($field);
                    if (isset($data[$field])) {
                        $filteredData[$field] = $data[$field];
                    }
                }
                $data = $filteredData;
            }

            return response()->json([
                'success' => true,
                'message' => 'Company settings retrieved successfully',
                'data' => $data,
                'meta' => [
                    'total' => 1,
                    'base_url' => $baseUrl,
                    'timestamp' => now()->toISOString(),
                    'version' => '1.0',
                    'requested_fields' => $fields ? explode(',', $fields) : 'all',
                    'source' => 'payroll_system'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving company settings: ' . $e->getMessage(),
                'data' => null,
                'meta' => [
                    'total' => 0,
                    'timestamp' => now()->toISOString(),
                    'version' => '1.0',
                    'error_type' => get_class($e)
                ]
            ], 500);
        }
    }
}