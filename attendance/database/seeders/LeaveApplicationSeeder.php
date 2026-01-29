<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LeaveApplication;
use Carbon\Carbon;

class LeaveApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Please create users first.');
            return;
        }

        foreach ($users as $user) {
            // Create some sample leave applications for each user
            LeaveApplication::create([
                'user_id' => $user->id,
                'email_id' => $user->email,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->subDays(8),
                'start_half_day' => 'none',
                'end_half_day' => 'none',
                'total_days' => 3,
                'status' => 'approved',
                'reason' => 'Family function',
                'leave_type' => 'casual',
                'financial_year' => $user->financial_year,
            ]);

            LeaveApplication::create([
                'user_id' => $user->id,
                'email_id' => $user->email,
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addDays(7),
                'start_half_day' => 'none',
                'end_half_day' => 'none',
                'total_days' => 3,
                'status' => 'pending',
                'reason' => 'Personal work',
                'leave_type' => 'sick',
                'financial_year' => $user->financial_year,
            ]);

            LeaveApplication::create([
                'user_id' => $user->id,
                'email_id' => $user->email,
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->subDays(25),
                'start_half_day' => 'none',
                'end_half_day' => 'none',
                'total_days' => 6,
                'status' => 'rejected',
                'reason' => 'Vacation',
                'leave_type' => 'annual',
                'financial_year' => $user->financial_year,
            ]);
        }
    }
}
