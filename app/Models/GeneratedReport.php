<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'generated_by',
        'director_id',
        'title',
        'type',
        'period_start',
        'period_end',
        'file_path',
        'file_name',
        'file_format',
        'file_size',
        'status',
        'error_message',
        'config',
        'statistics',
        'download_count',
        'last_downloaded_at',
        'is_scheduled',
        'schedule_frequency',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'config' => 'array',
            'statistics' => 'array',
            'last_downloaded_at' => 'datetime',
            'is_scheduled' => 'boolean',
        ];
    }

    const TYPE_MONTHLY = 'monthly';
    const TYPE_WEEKLY = 'weekly';
    const TYPE_DAILY = 'daily';
    const TYPE_CUSTOM = 'custom';
    const TYPE_APPOINTMENTS = 'appointments';
    const TYPE_MEETINGS = 'meetings';
    const TYPE_STATISTICS = 'statistics';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * User who generated this report
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Director this report is for
     */
    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Track download
     */
    public function trackDownload(): bool
    {
        $this->download_count++;
        $this->last_downloaded_at = now();
        
        return $this->save();
    }

    /**
     * Check if report is ready
     */
    public function isReady(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if report failed
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get full file path
     */
    public function getFullPath(): ?string
    {
        return $this->file_path ? storage_path('app/' . $this->file_path) : null;
    }

    /**
     * Scope for completed reports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for a specific director
     */
    public function scopeForDirector($query, int $directorId)
    {
        return $query->where('director_id', $directorId);
    }

    /**
     * Get report types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_MONTHLY => 'Monthly Report',
            self::TYPE_WEEKLY => 'Weekly Report',
            self::TYPE_DAILY => 'Daily Report',
            self::TYPE_CUSTOM => 'Custom Report',
            self::TYPE_APPOINTMENTS => 'Appointments Report',
            self::TYPE_MEETINGS => 'Meetings Report',
            self::TYPE_STATISTICS => 'Statistics Report',
        ];
    }
}
