<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialYearReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_year_id',
        'report_type',
        'report_name',
        'report_data',
        'file_path',
        'file_type',
        'generated_at',
        'generated_by',
        'file_size',
        'status',
        'error_message',
    ];

    protected $casts = [
        'report_data' => 'array',
        'generated_at' => 'datetime',
        'file_size' => 'integer',
    ];

    /**
     * Financial year relationship
     */
    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class);
    }

    /**
     * User who generated the report
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Check if report file exists
     */
    public function fileExists()
    {
        return $this->file_path && file_exists(storage_path('app/' . $this->file_path));
    }

    /**
     * Get file URL for download
     */
    public function getDownloadUrl()
    {
        if (!$this->fileExists()) {
            return null;
        }
        
        return route('financial-year.reports.download', $this->id);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'processing' => 'badge-info',
            'completed' => 'badge-success',
            'failed' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Scope for completed reports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed reports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
