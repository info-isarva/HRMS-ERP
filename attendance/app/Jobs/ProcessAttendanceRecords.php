<?php

namespace App\Jobs;

use App\Models\AttendanceBatch;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAttendanceRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batchId;
    protected $chunk;
    protected $month;
    protected $year;
    protected $isLocking;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct($batchId, $chunk, $month, $year, $isLocking = false)
    {
        $this->batchId = $batchId;
        $this->chunk = $chunk;
        $this->month = $month;
        $this->year = $year;
        $this->isLocking = $isLocking;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $batch = AttendanceBatch::find($this->batchId);
        
        if (!$batch || $batch->status === 'failed') {
            Log::error('AttendanceBatch not found or has failed', ['batch_id' => $this->batchId]);
            return;
        }

        try {
            DB::beginTransaction();

            $processedCount = 0;
            $failedCount = 0;

            foreach ($this->chunk as $record) {
                try {
                    $attendanceRecord = AttendanceRecord::updateOrCreate(
                        [
                            'user_id' => $record['user_id'],
                            'date' => $record['date'],
                        ],
                        [
                            'status' => $record['status'],
                            'leave_type_id' => $record['leave_type_id'] ?? null,
                            'leave_application_id' => $record['leave_application_id'] ?? null,
                            'public_holiday_id' => $record['public_holiday_id'] ?? null,
                            'month' => $this->month,
                            'year' => $this->year,
                            'batch_id' => $this->batchId,
                            'is_locked' => $this->isLocking,
                            'locked_at' => $this->isLocking ? now() : null,
                            'locked_by' => $this->isLocking ? $batch->initiated_by : null,
                        ]
                    );

                    $processedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error('Failed to process attendance record', [
                        'user_id' => $record['user_id'] ?? null,
                        'date' => $record['date'] ?? null,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Update the batch with progress
            $batch->increment('processed_records', $processedCount);
            $batch->increment('failed_records', $failedCount);

            // Check if all records are processed
            if ($batch->processed_records + $batch->failed_records >= $batch->total_records) {
                $batch->status = 'completed';
                $batch->completed_at = now();
                $batch->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $batch->status = 'failed';
            $batch->save();
            
            Log::error('Failed to process attendance batch chunk', [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
