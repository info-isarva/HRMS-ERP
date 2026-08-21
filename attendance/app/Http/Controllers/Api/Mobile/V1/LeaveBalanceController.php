<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Services\PayrollLeaveService;
use App\Support\MobileLeaveApplicationSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveBalanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $fy = MobileLeaveApplicationSupport::resolveOperationalFinancialYearName(
            $request->query('financial_year')
        );
        $payroll = new PayrollLeaveService();
        $result = $payroll->getEmployeeLeaveBalance($user, $fy);

        $types = collect($result['leave_types'] ?? [])->map(function ($row) {
            $allocated = $row->effective_days ?? $row->days_count ?? 0;
            $used = $row->used ?? 0;
            $balance = $row->balance ?? max(0, $allocated - $used);

            return [
                'leave_type_id' => $row->id,
                'name' => $row->name,
                'code' => $row->code ?? null,
                'allocated' => $allocated,
                'used' => $used,
                'balance' => $balance,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Leave balances retrieved successfully',
            'data' => [
                'financial_year' => $fy,
                'source' => $result['source'] ?? null,
                'leave_types' => $types,
            ],
        ]);
    }
}
