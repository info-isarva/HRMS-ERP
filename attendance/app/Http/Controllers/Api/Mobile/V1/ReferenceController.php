<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Api\PermissionsApiController;
use App\Http\Controllers\Controller;
use App\Models\FinancialYear;
use App\Models\PublicHoliday;
use App\Support\MobileLeaveApplicationSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    public function financialYears(): JsonResponse
    {
        $currentName = MobileLeaveApplicationSupport::currentFinancialYearName();

        $rows = FinancialYear::orderByDesc('start_date')
            ->get(['id', 'name', 'start_date', 'end_date', 'is_active', 'status'])
            ->map(fn ($fy) => [
                'id' => $fy->id,
                'name' => $fy->name,
                'start_date' => $fy->start_date?->format('Y-m-d'),
                'end_date' => $fy->end_date?->format('Y-m-d'),
                'is_current' => (bool) $fy->is_active,
                'is_closed' => ($fy->status === 'close' || $fy->status === 'closed'),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'current_financial_year' => $currentName,
                'financial_years' => $rows,
            ],
        ]);
    }

    public function holidays(Request $request): JsonResponse
    {
        $fy = MobileLeaveApplicationSupport::resolveOperationalFinancialYearName(
            $request->query('financial_year')
        );

        $holidays = PublicHoliday::query()
            ->where('status', 'active')
            ->where('financial_year', $fy)
            ->orderBy('date')
            ->get(['id', 'name', 'date', 'financial_year', 'type', 'is_national', 'description'])
            ->map(fn ($h) => [
                'id' => $h->id,
                'name' => $h->name,
                'date' => $h->date?->format('Y-m-d'),
                'financial_year' => $h->financial_year,
                'type' => $h->type,
                'is_national' => (bool) $h->is_national,
                'description' => $h->description,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'financial_year' => $fy,
                'holidays' => $holidays,
            ],
        ]);
    }

    public function permissions(): JsonResponse
    {
        return app(PermissionsApiController::class)->index();
    }
}
