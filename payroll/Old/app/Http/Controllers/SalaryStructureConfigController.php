<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use App\Models\SalaryStructureConfig;
use Illuminate\Http\Request;

class SalaryStructureConfigController extends Controller
{
    public function index()
    {
        $configs = SalaryStructureConfig::with('salaryComponent')->get()->keyBy('salary_component_id');
        $salaryComponents = SalaryComponent::where('status', 1)->get();
        return view('settings.salary-structure', compact('configs', 'salaryComponents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'configs' => 'required|array',
            'configs.*.salary_component_id' => 'required|exists:salary_components,id',
            'configs.*.calculation_type' => 'required|in:percentage,fixed',
            'configs.*.value' => 'required|numeric|min:0',
            'configs.*.percentage_of' => 'nullable|in:ctc,basic',
        ]);

        // Remove existing configs to replace with new ones (simplifies logic)
        // Or updateExisting. Let's iterate and update/create.
        
        foreach ($request->configs as $configData) {
            SalaryStructureConfig::updateOrCreate(
                ['salary_component_id' => $configData['salary_component_id']],
                [
                    'calculation_type' => $configData['calculation_type'],
                    'value' => $configData['value'],
                    'percentage_of' => $configData['percentage_of'] ?? 'ctc',
                    'status' => 1,
                ]
            );
        }

        return redirect()->back()->with('success', 'Salary structure configuration updated successfully.');
    }

    public function getConfigs()
    {
        $configs = SalaryStructureConfig::where('status', 1)->get();
        return response()->json([
            'success' => true,
            'data' => $configs
        ]);
    }
}
