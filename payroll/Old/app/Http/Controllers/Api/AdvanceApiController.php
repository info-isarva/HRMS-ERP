<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeBasicDetail;
use App\Models\EmployeeAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdvanceApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        try {
            $employee = EmployeeBasicDetail::where('email', $user->email)->first();

            if (!$employee) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No employee record found.',
                ]);
            }

            $advances = EmployeeAdvance::with('deductions')
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Format data
            $formatted = $advances->map(function ($adv) {
                return [
                    'id' => $adv->id,
                    'advance_amount' => $adv->advance_amount,
                    'tenure_months' => $adv->tenure_months,
                    'monthly_deduction' => $adv->monthly_deduction,
                    'start_date' => $adv->start_date ? $adv->start_date->toDateString() : null,
                    'end_date' => $adv->end_date ? $adv->end_date->toDateString() : null,
                    'total_deducted' => $adv->total_deducted,
                    'remaining_amount' => $adv->remaining_amount,
                    'status' => $adv->status,
                    'notes' => $adv->notes,
                    'deductions' => $adv->deductions->map(function ($ded) {
                        return [
                            'amount' => $ded->amount,
                            'month' => $ded->month,
                            'year' => $ded->year,
                            'created_at' => $ded->created_at->toDateTimeString(),
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted,
            ]);
        } catch (\Throwable $e) {
            Log::error('Sanctum Advances API failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve advances.',
            ], 500);
        }
    }
}
