<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ManualNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:process-scheduled';

    /**
     * The console command description.
     */
    protected $description = 'Process scheduled notifications and activate them based on schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled notifications...');
        Log::info('Scheduler starting: ProcessScheduledNotifications');
        
        $now = Carbon::now();
        Log::info("Scheduler Current Time: {$now}");

        $processedCount = 0;
        $expiredCount = 0;
        
        // Activate scheduled notifications that should start now
        $query = ManualNotification::where('status', 'scheduled')
            ->where('start_date', '<=', $now);
            
        // Log the query sql for debugging
        // Log::info("Scheduler Query: " . $query->toSql());
        // Log::info("Scheduler Query Bindings: " . json_encode($query->getBindings()));
            
        $scheduledNotifications = $query->get();
        
        Log::info("Scheduler Found {$scheduledNotifications->count()} scheduled notifications to activate.");

        foreach ($scheduledNotifications as $notification) {
            try {
                Log::info("Activating notification ID: {$notification->id}, Title: {$notification->title}, Start Date: {$notification->start_date}");
                $notification->update(['status' => 'active']);
                $this->info("Activated notification: {$notification->title}");
                
                // Send high priority notifications via email/SMS
                if ($notification->priority === 'high') {
                    $this->sendHighPriorityNotifications($notification);
                }
                
                $processedCount++;
                
            } catch (\Exception $e) {
                $this->error("Failed to activate notification {$notification->id}: {$e->getMessage()}");
                Log::error("Failed to activate notification {$notification->id}", ['error' => $e->getMessage()]);
            }
        }
        
        // Expire active notifications that have passed their end date
        $expiredNotifications = ManualNotification::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<', $now)
            ->get();
            
        foreach ($expiredNotifications as $notification) {
            try {
                $notification->update(['status' => 'completed']);
                $this->info("Expired notification: {$notification->title}");
                $expiredCount++;
                
            } catch (\Exception $e) {
                $this->error("Failed to expire notification {$notification->id}: {$e->getMessage()}");
                Log::error("Failed to expire notification {$notification->id}", ['error' => $e->getMessage()]);
            }
        }
        
        // Handle recurring notifications
        $this->processRecurringNotifications($now);
        
        $this->info("Processed {$processedCount} scheduled notifications");
        $this->info("Expired {$expiredCount} notifications");
        
        return 0;
    }
    
    /**
     * Process recurring notifications
     */
    private function processRecurringNotifications(Carbon $now)
    {
        $recurringNotifications = ManualNotification::where('status', 'active')
            ->whereIn('recurrence_type', ['daily', 'weekly', 'monthly'])
            ->get();
            
        foreach ($recurringNotifications as $notification) {
            try {
                if ($this->shouldCreateRecurringInstance($notification, $now)) {
                    $this->createRecurringInstance($notification, $now);
                }
            } catch (\Exception $e) {
                $this->error("Failed to process recurring notification {$notification->id}: {$e->getMessage()}");
                Log::error("Failed to process recurring notification {$notification->id}", ['error' => $e->getMessage()]);
            }
        }
    }
    
    /**
     * Check if a recurring notification should create a new instance
     */
    private function shouldCreateRecurringInstance(ManualNotification $notification, Carbon $now): bool
    {
        // Check if we've passed the recurrence end date
        if ($notification->recurrence_end_date && $now->gt($notification->recurrence_end_date)) {
            $notification->update(['status' => 'completed']);
            return false;
        }
        
        $lastStart = $notification->start_date;
        $interval = $notification->recurrence_interval;
        
        switch ($notification->recurrence_type) {
            case 'daily':
                $nextScheduled = $lastStart->copy()->addDays($interval);
                break;
            case 'weekly':
                $nextScheduled = $lastStart->copy()->addWeeks($interval);
                break;
            case 'monthly':
                $nextScheduled = $lastStart->copy()->addMonths($interval);
                break;
            default:
                return false;
        }
        
        return $now->gte($nextScheduled);
    }
    
    /**
     * Create a new instance of a recurring notification
     */
    private function createRecurringInstance(ManualNotification $notification, Carbon $now)
    {
        // For now, we'll just reset the read status by clearing the notification_reads table
        // In a more complex system, you might want to create new notification instances
        
        $notification->reads()->delete();
        
        // Update the start date for the next occurrence
        switch ($notification->recurrence_type) {
            case 'daily':
                $newStart = $notification->start_date->copy()->addDays($notification->recurrence_interval);
                break;
            case 'weekly':
                $newStart = $notification->start_date->copy()->addWeeks($notification->recurrence_interval);
                break;
            case 'monthly':
                $newStart = $notification->start_date->copy()->addMonths($notification->recurrence_interval);
                break;
        }
        
        $notification->update(['start_date' => $newStart]);
        
        $this->info("Created recurring instance for: {$notification->title}");
        
        // Send notifications for high priority recurring notifications
        if ($notification->priority === 'high') {
            $this->sendHighPriorityNotifications($notification);
        }
    }
    
    /**
     * Send high priority notifications via email/SMS
     */
    private function sendHighPriorityNotifications(ManualNotification $notification)
    {
        if (!$notification->send_email && !$notification->send_sms) {
            return;
        }
        
        try {
            $targetedUsers = $notification->getTargetedUsers();
            
            foreach ($targetedUsers as $user) {
                if ($notification->send_email && $user->email) {
                    $this->sendEmailNotification($user, $notification);
                }
                
                if ($notification->send_sms && $user->phone) {
                    $this->sendSmsNotification($user, $notification);
                }
            }
            
            $this->info("Sent high priority notifications for: {$notification->title}");
            
        } catch (\Exception $e) {
            $this->error("Failed to send high priority notifications: {$e->getMessage()}");
            Log::error("Failed to send high priority notifications for notification {$notification->id}", ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification($user, ManualNotification $notification)
    {
        try {
            Mail::send('emails.high-priority-notification', [
                'user' => $user,
                'notification' => $notification
            ], function ($message) use ($user, $notification) {
                $message->to($user->email, $user->name)
                        ->subject("High Priority: {$notification->title}");
            });
            
        } catch (\Exception $e) {
            Log::error("Failed to send email notification to {$user->email}", ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Send SMS notification (placeholder - implement based on your SMS service)
     */
    private function sendSmsNotification($user, ManualNotification $notification)
    {
        try {
            // Implement SMS sending logic here based on your SMS service
            // For example: Twilio, AWS SNS, etc.
            
            $message = "High Priority Alert: {$notification->title}\n{$notification->message}";
            
            // SMS sending code would go here
            Log::info("SMS notification sent to {$user->phone}", [
                'notification_id' => $notification->id,
                'user_id' => $user->id
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to send SMS notification to {$user->phone}", ['error' => $e->getMessage()]);
        }
    }
}
