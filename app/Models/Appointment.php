<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'director_id',
        'created_by',
        'requester_name',
        'requester_email',
        'requester_phone',
        'requester_organization',
        'requester_designation',
        'purpose',
        'description',
        'meeting_type',
        'requested_date',
        'requested_start_time',
        'requested_end_time',
        'approved_date',
        'approved_start_time',
        'approved_end_time',
        'duration_minutes',
        'status',
        'admin_notes',
        'rejection_reason',
        'requester_notified',
        'notified_at',
        'source',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'approved_date' => 'date',
            'notified_at' => 'datetime',
            'requester_notified' => 'boolean',
        ];
    }

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RESCHEDULED = 'rescheduled';

    // Meeting type constants
    const TYPE_OFFICE = 'office_meeting';
    const TYPE_BOARDROOM = 'boardroom';
    const TYPE_ONLINE = 'online';
    const TYPE_SITE_VISIT = 'site_visit';
    const TYPE_LAB_VISIT = 'lab_visit';
    const TYPE_OTHER = 'other';

    /**
     * Director who receives this appointment
     */
    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * User who created this appointment
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Meeting schedule created from this appointment
     */
    public function meetingSchedule()
    {
        return $this->hasOne(MeetingSchedule::class, 'appointment_id');
    }

    /**
     * Check if appointment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if appointment is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Approve the appointment
     */
    public function approve(?string $date = null, ?string $startTime = null, ?string $endTime = null): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_date = $date ?? $this->requested_date;
        $this->approved_start_time = $startTime ?? $this->requested_start_time;
        $this->approved_end_time = $endTime ?? $this->requested_end_time;
        
        return $this->save();
    }

    /**
     * Reject the appointment
     */
    public function reject(string $reason = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejection_reason = $reason;
        
        return $this->save();
    }

    /**
     * Mark as completed
     */
    public function complete(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        return $this->save();
    }

    /**
     * Scope for pending appointments
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved appointments
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for today's appointments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('requested_date', today());
    }

    /**
     * Scope for upcoming appointments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('requested_date', '>=', today())
                     ->orderBy('requested_date')
                     ->orderBy('requested_start_time');
    }

    /**
     * Scope for a specific director
     */
    public function scopeForDirector($query, int $directorId)
    {
        return $query->where('director_id', $directorId);
    }

    /**
     * Get meeting type options
     */
    public static function getMeetingTypes(): array
    {
        return [
            self::TYPE_OFFICE => 'Office Meeting',
            self::TYPE_BOARDROOM => 'Board Room',
            self::TYPE_ONLINE => 'Online Meeting',
            self::TYPE_SITE_VISIT => 'Site Visit',
            self::TYPE_LAB_VISIT => 'Lab Visit',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get status options
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RESCHEDULED => 'Rescheduled',
        ];
    }
}
