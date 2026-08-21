<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PayrollLeaveService;
use App\Models\User;
use App\Models\LeaveApplication;

class TestLeaveBalance extends Command
{
    protected $signature = 'test:leave-balance {email}';
    protected $description = 'Test leave balance calculation for a specific employee';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Testing Leave Balance Calculation for: {$email}");
        $this->info("================================================");

        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found with email: {$email}");
            return 1;
        }

        $this->info("✅ Found user: {$user->name} (ID: {$user->id}, Email: {$user->email})");

        // Test the leave balance service
        $leaveService = new PayrollLeaveService();

        $this->info("Testing leave balance calculation...");
        $leaveBalance = $leaveService->getEmployeeLeaveBalance($user);

        if ($leaveBalance['success']) {
            $this->info("✅ Leave balance calculation successful");
            $this->info("Source: " . ($leaveBalance['source'] ?? 'unknown'));
            $this->info("Financial Year: " . ($leaveBalance['financial_year'] ?? 'unknown'));
            
            $this->info("\nLeave Types and Balances:");
            $this->info("========================");
            
            foreach ($leaveBalance['leave_types'] as $leaveType) {
                $this->info("Leave Type ID: {$leaveType->id}");
                $this->info("Name: {$leaveType->name}");
                $this->info("Allocated (Effective Days): {$leaveType->effective_days}");
                $this->info("Used: {$leaveType->used}");
                $this->info("Balance: {$leaveType->balance}");
                $this->info("---");
            }
            
            // Test specific leave type (leave_type_id = 1)
            $this->info("\nTesting specific leave type (ID: 1):");
            $this->info("===================================");
            
            $leaveType1 = $leaveBalance['leave_types']->firstWhere('id', 1);
            if ($leaveType1) {
                $this->info("✅ Leave Type 1 found:");
                $this->info("  - Allocated: {$leaveType1->effective_days} days");
                $this->info("  - Used: {$leaveType1->used} days");
                $this->info("  - Balance: {$leaveType1->balance} days");
                
                // Verify with manual calculation
                $this->info("\nManual verification:");
                $approvedLeaves = LeaveApplication::whereHas('user', function($query) use ($user) {
                        $query->where('email', $user->email);
                    })
                    ->where('leave_type_id', 1)
                    ->where('status', 'approved')
                    ->get(['total_days']);
                    
                $manualUsed = $approvedLeaves->sum('total_days');
                $this->info("  - Manual calculation of used days: {$manualUsed}");
                
                if ($leaveType1->used == $manualUsed) {
                    $this->info("  ✅ Used days calculation matches!");
                } else {
                    $this->error("  ❌ Used days mismatch! Service: {$leaveType1->used}, Manual: {$manualUsed}");
                }
                
                // Show individual applications
                $this->info("\nIndividual approved applications for leave type 1:");
                foreach ($approvedLeaves as $app) {
                    $this->info("  - Application: {$app->total_days} days");
                }
            } else {
                $this->error("❌ Leave Type 1 not found in results");
            }
            
        } else {
            $this->error("❌ Leave balance calculation failed");
            if (isset($leaveBalance['message'])) {
                $this->error("Error: " . $leaveBalance['message']);
            }
        }

        $this->info("\n=== Direct Email Test ===");
        $emailBalance = $leaveService->getEmployeeLeaveBalanceByEmail($email);

        if ($emailBalance['success']) {
            $this->info("✅ Direct email lookup successful");
            $leaveType1Email = $emailBalance['leave_types']->firstWhere('id', 1);
            if ($leaveType1Email) {
                $this->info("Leave Type 1 via email lookup:");
                $this->info("  - Used: {$leaveType1Email->used} days");
                $this->info("  - Balance: {$leaveType1Email->balance} days");
            }
        } else {
            $this->error("❌ Direct email lookup failed");
        }

        $this->info("\nTest completed!");
        return 0;
    }
}