<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaveTypeController extends Controller
{
    /**
     * Get all leave types with basic details
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $leaveTypes = LeaveType::with(['departments:id,name,code,api_department_id'])
                ->select('id', 'name', 'code', 'description', 'days_count', 'is_active', 'financial_year')
                ->get();

            $data = $leaveTypes->map(function ($leaveType) {
                return [
                    'id' => $leaveType->id,
                    'leave_type_name' => $leaveType->name,
                    'leave_type_code' => $leaveType->code,
                    'days_allowed' => $leaveType->days_count,
                    'status' => $leaveType->is_active ? 'Active' : 'Inactive',
                    'description' => $leaveType->description ?? '',
                    'financial_year' => $leaveType->financial_year,
                    'assigned_departments' => $leaveType->departments->map(function ($dept) {
                        return [
                            'id' => $dept->id,
                            'name' => $dept->name,
                            'code' => $dept->code,
                            'api_department_id' => $dept->api_department_id
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Leave types retrieved successfully',
                'data' => $data,
                'total_count' => $data->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve leave types',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}