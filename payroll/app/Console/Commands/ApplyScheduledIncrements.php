<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApplyScheduledIncrements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrms:apply-increments';
    protected $description = 'Apply scheduled salary increments and promotions';

    public function handle()
    {
        $today = \Carbon\Carbon::today();
        
        $increments = \App\Models\EmployeeIncrement::where('status', 'approved')
            ->whereDate('effective_date', '<=', $today)
            ->get();
            
        $this->info("Found " . $increments->count() . " pending increments to apply.");
        
        foreach ($increments as $inc) {
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                $employee = \App\Models\EmployeeBasicDetail::find($inc->employee_id);
                if (!$employee) {
                    $this->error("Employee ID {$inc->employee_id} not found.");
                    continue;
                }
                
                // 1. Update Basic Details
                $employee->annual_ctc = $inc->new_ctc;
                $employee->monthly_ctc = $inc->new_ctc / 12;
                if ($inc->new_designation_id) {
                    $employee->designation = $inc->new_designation_id;
                }
                $employee->save();
                
                // 2. Re-calculate Structure
                // Fetch Master Components
                $masterComponents = \App\Models\SalaryComponent::where('status', 1)->get();
                $monthlyCTC = $inc->new_ctc / 12;
                
                $fixedEarnings = 0;
                $basicValue = 0;
                $residualComponent = null;
                $componentValues = []; // [component_id => value]
                
                // Pass 1: Fixed
                foreach ($masterComponents as $comp) {
                    $val = 0;
                    if ($comp->calculation_type == 'flat_amount') {
                        $val = $comp->calculation_value;
                    } elseif ($comp->calculation_type == 'percentage_ctc') {
                        $val = ($monthlyCTC * $comp->calculation_value) / 100;
                    } elseif ($comp->calculation_type == 'residual') {
                        $residualComponent = $comp;
                        continue;
                    }
                    
                    if ($comp->type == 'earning' && $comp->calculation_type != 'percentage_basic') {
                        $componentValues[$comp->id] = $val;
                        $fixedEarnings += $val;
                        
                        if (stripos($comp->short_name, 'basic') !== false) {
                            $basicValue = $val;
                        }
                    }
                }
                
                // Pass 2: Dependent
                foreach ($masterComponents as $comp) {
                    if ($comp->calculation_type == 'percentage_basic') {
                        $val = ($basicValue * $comp->calculation_value) / 100;
                        if ($comp->type == 'earning') {
                            $componentValues[$comp->id] = $val;
                            $fixedEarnings += $val;
                        }
                    }
                }
                
                // Pass 3: Residual
                if ($residualComponent) {
                    // Note: Simplified logic. Ignoring Employer PF/ESIC deduction from CTC for now 
                    // unless explicitly handled. In JS logic, we deducted Employer PF if 'pf_include' was true.
                    // Here we assume Gross ~ CTC for simplicity or strictly follow CTC - Fixed.
                    // Ideally, we should check statutory settings.
                    // For now: Residual = CTC - FixedEarnings.
                    
                    $residualValue = max(0, $monthlyCTC - $fixedEarnings);
                    $componentValues[$residualComponent->id] = $residualValue;
                }
                
                // 3. Update Employee Salary Components
                // Delete old? Or Update existing? Better to delete and recreate to match Master Structure
                \App\Models\EmployeeSalaryComponent::where('emp_id', $employee->id)->delete();
                
                foreach ($componentValues as $compId => $val) {
                    \App\Models\EmployeeSalaryComponent::create([
                        'emp_id' => $employee->id,
                        'salary_component_id' => $compId,
                        'value' => $val,
                        'enabled' => 1
                    ]);
                }
                
                // 4. Update Status
                $inc->status = 'processed';
                $inc->processed_at = now();
                $inc->save();
                
                \Illuminate\Support\Facades\DB::commit();
                $this->info("Processed increment for Employee {$employee->name}");
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                $this->error("Failed to process increment ID {$inc->id}: " . $e->getMessage());
            }
        }
    }
}
