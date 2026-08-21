<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeBasicDetail;
use App\Models\EmployeePersonalDetail;
use App\Models\EmployeeBankDetail;
use App\Models\EmployeeStatutoryComponent;
use App\Models\EmployeeSalaryComponent;
use App\Models\Department;
use App\Models\PositionType;
use App\Models\Role;
use App\Models\EmployeeStatus;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $indianNames = [
            'Arjun Sharma', 'Deepika Iyer', 'Rohan Gupta', 'Sneha Patil', 'Amit Verma',
            'Priya Reddy', 'Vikram Singh', 'Kavita Joshi', 'Suresh Kumar', 'Anjali Nair',
            'Rahul Mehra', 'Pooja Agarwal', 'Manish Pandey', 'Divya Saxena', 'Sanjay Malhotra',
            'Neha Kapoor', 'Alok Tiwari', 'Rashmi Chauhan', 'Vivek Dubey', 'Shikha Mishra',
            'Abhishek Bose', 'Meera Kulkarni', 'Karan Sethi', 'Swati Deshmukh', 'Pankaj Rathore',
            'Ritu Singhania', 'Gaurav Bhatt', 'Suman Das', 'Aditya Roy', 'Tanvi Mahajan', 'Varun Gill'
        ];

        $departments = [1, 2, 3, 4, 15, 16]; 
        $designations = [15, 16, 24, 25, 4, 10]; 
        $locations = [1, 2, 3]; 

        foreach ($indianNames as $index => $name) {
            DB::beginTransaction();
            try {
                $employeeId = 'EMP' . str_pad($index + 200, 4, '0', STR_PAD_LEFT);
                
                while (EmployeeBasicDetail::where('employee_id', $employeeId)->exists()) {
                    $employeeId = 'EMP' . rand(2000, 9999);
                }

                $ctc = rand(300000, 1500000); 
                $monthlyCtc = round($ctc / 12, 2);

                // Basic Details
                $basic = EmployeeBasicDetail::create([
                    'employee_id' => $employeeId,
                    'name' => strtoupper($name),
                    'email' => strtolower(str_replace(' ', '.', $name)) . '@isarva.in',
                    'contact_number' => '9' . rand(100000000, 999999999),
                    'annual_ctc' => $ctc,
                    'monthly_ctc' => $monthlyCtc,
                    'gender' => ($index % 2 == 0) ? 1 : 2,
                    'marital_status' => ($index % 3 == 0) ? 2 : 1,
                    'designation' => $designations[array_rand($designations)],
                    'department' => $departments[array_rand($departments)],
                    'location_id' => $locations[array_rand($locations)],
                    'date_of_joining' => Carbon::now()->subMonths(rand(1, 24))->format('Y-m-d'),
                    'status' => 1,
                    'role' => 2,
                    'ot_status' => 'no',
                    'incentive_status' => 'no',
                    'enable_self_portal' => 1,
                    'exclude_from_payroll' => 0,
                    'created_by' => 1,
                    'updated_by' => 1
                ]);

                // Personal Details
                EmployeePersonalDetail::create([
                    'emp_id' => $basic->id,
                    'father_name' => strtoupper(explode(' ', $name)[1] . ' ' . 'SR'),
                    'blood_group' => rand(1, 8),
                    'address' => 'Sample Address in India ' . ($index + 1),
                    'emergency_contact_name' => 'EMERGENCY CONTACT ' . strtoupper(explode(' ', $name)[0]),
                    'emergency_contact_number' => '8' . rand(100000000, 999999999),
                    'aadhaar_number' => rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999),
                    'pan_number' => 'ABCDE' . rand(1000, 9999) . 'Z',
                    'created_by' => 1,
                    'updated_by' => 1
                ]);

                // Bank Details
                EmployeeBankDetail::create([
                    'emp_id' => $basic->id,
                    'type_of_payment' => 1, // 1=Bank transfer
                    'bank_name' => ['HDFC Bank', 'ICICI Bank', 'SBI', 'Axis Bank'][rand(0, 3)],
                    'account_number' => (string)rand(100000000000, 999999999999),
                    'ifsc_code' => 'BANK000' . rand(1000, 9999),
                    'branch' => 'Main Branch',
                    'transaction_type' => 1, // 1=NEFT TRANSFER
                    'created_by' => 1,
                    'updated_by' => 1
                ]);

                // Salary Components
                $basicAmount = round($monthlyCtc * 0.5, 2); 
                $da = round($monthlyCtc * 0.1, 2); 
                $hra = round($monthlyCtc * 0.2, 2); 
                $other = round($monthlyCtc - ($basicAmount + $da + $hra), 2);

                EmployeeSalaryComponent::create(['emp_id' => $basic->id, 'salary_component_id' => 1, 'value' => $basicAmount, 'created_by' => 1, 'updated_by' => 1]);
                EmployeeSalaryComponent::create(['emp_id' => $basic->id, 'salary_component_id' => 2, 'value' => $da, 'created_by' => 1, 'updated_by' => 1]);
                EmployeeSalaryComponent::create(['emp_id' => $basic->id, 'salary_component_id' => 3, 'value' => $hra, 'created_by' => 1, 'updated_by' => 1]);
                EmployeeSalaryComponent::create(['emp_id' => $basic->id, 'salary_component_id' => 4, 'value' => $other, 'created_by' => 1, 'updated_by' => 1]);

                // Statutory Components
                // PF
                EmployeeStatutoryComponent::create([
                    'emp_id' => $basic->id,
                    'statutory_component_id' => 1,
                    'value' => 1800,
                    'epf_option' => 'restrict_15000',
                    'created_by' => 1,
                    'updated_by' => 1
                ]);
                // ESI
                if ($monthlyCtc < 21000) {
                    EmployeeStatutoryComponent::create([
                        'emp_id' => $basic->id,
                        'statutory_component_id' => 2,
                        'value' => round($monthlyCtc * 0.0075, 2),
                        'created_by' => 1,
                        'updated_by' => 1
                    ]);
                }
                // PT
                EmployeeStatutoryComponent::create([
                    'emp_id' => $basic->id,
                    'statutory_component_id' => 4,
                    'value' => 200,
                    'created_by' => 1,
                    'updated_by' => 1
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                echo "Error seeding employee $name: " . $e->getMessage() . "\n";
            }
        }
    }
}
