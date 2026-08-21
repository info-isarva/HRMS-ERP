<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FinancialYearSwitchController extends Controller
{
    /**
     * Switch to a different financial year
     */
    public function switch(Request $request)
    {
        try {
            $request->validate([
                'financial_year_id' => 'required|exists:financial_years,id'
            ]);
            
            $financialYear = FinancialYear::findOrFail($request->financial_year_id);
            
            // Store selected FY in session
            Session::put('selected_financial_year_id', $financialYear->id);
            
            // Determine if the selected FY is editable
            $currentFY = FinancialYear::where('is_current', true)->first();
            $isEditable = $currentFY && $financialYear->id === $currentFY->id;
            
            return response()->json([
                'success' => true,
                'message' => 'Financial year switched successfully',
                'data' => [
                    'selected_fy' => [
                        'id' => $financialYear->id,
                        'name' => $financialYear->name,
                        'start_date' => $financialYear->start_date->format('M d, Y'),
                        'end_date' => $financialYear->end_date->format('M d, Y'),
                        'is_current' => $financialYear->is_current,
                        'is_closed' => $financialYear->is_closed
                    ],
                    'is_editable' => $isEditable,
                    'current_fy_id' => $currentFY ? $currentFY->id : null
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to switch financial year: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get current selected financial year
     */
    public function current()
    {
        try {
            $selectedFyId = Session::get('selected_financial_year_id');
            $selectedFY = $selectedFyId ? FinancialYear::find($selectedFyId) : null;
            
            if (!$selectedFY) {
                $selectedFY = FinancialYear::where('is_current', true)->first();
                if ($selectedFY) {
                    Session::put('selected_financial_year_id', $selectedFY->id);
                }
            }
            
            if (!$selectedFY) {
                return response()->json([
                    'success' => false,
                    'message' => 'No financial year found'
                ], 404);
            }
            
            $currentFY = FinancialYear::where('is_current', true)->first();
            $isEditable = $currentFY && $selectedFY->id === $currentFY->id;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'selected_fy' => [
                        'id' => $selectedFY->id,
                        'name' => $selectedFY->name,
                        'start_date' => $selectedFY->start_date->format('M d, Y'),
                        'end_date' => $selectedFY->end_date->format('M d, Y'),
                        'is_current' => $selectedFY->is_current,
                        'is_closed' => $selectedFY->is_closed
                    ],
                    'is_editable' => $isEditable,
                    'current_fy_id' => $currentFY ? $currentFY->id : null
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get current financial year: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reset to current financial year
     */
    public function resetToCurrent()
    {
        try {
            $currentFY = FinancialYear::where('is_current', true)->first();
            
            if (!$currentFY) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current financial year found'
                ], 404);
            }
            
            Session::put('selected_financial_year_id', $currentFY->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Reset to current financial year',
                'data' => [
                    'selected_fy' => [
                        'id' => $currentFY->id,
                        'name' => $currentFY->name,
                        'start_date' => $currentFY->start_date->format('M d, Y'),
                        'end_date' => $currentFY->end_date->format('M d, Y'),
                        'is_current' => true,
                        'is_closed' => $currentFY->is_closed
                    ],
                    'is_editable' => true,
                    'current_fy_id' => $currentFY->id
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset to current financial year: ' . $e->getMessage()
            ], 500);
        }
    }
}
