<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assigned_by',
        'title',
        'description',
        'priority',
        'category',
        'tags',
        'due_date',
        'due_time',
        'is_completed',
        'completed_at',
        'related_meeting_id',
        'related_appointment_id',
        'has_reminder',
        'reminder_at',
        'reminder_sent',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'tags' => 'array',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
            'has_reminder' => 'boolean',
            'reminder_at' => 'datetime',
            'reminder_sent' => 'boolean',
        ];
    }

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * User who owns this todo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User who assigned this todo
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Related meeting
     */
    public function relatedMeeting()
    {
        return $this->belongsTo(MeetingSchedule::class, 'related_meeting_id');
    }

    /**
     * Related appointment
     */
    public function relatedAppointment()
    {
        return $this->belongsTo(Appointment::class, 'related_appointment_id');
    }

    /**
     * Toggle completion status
     */
    public function toggle(): bool
    {
        $this->is_completed = !$this->is_completed;
        $this->completed_at = $this->is_completed ? now() : null;
        
        return $this->save();
    }

    /**
     * Mark as completed
     */
    public function complete(): bool
    {
        $this->is_completed = true;
        $this->completed_at = now();
        
        return $this->save();
    }

    /**
     * Mark as incomplete
     */
    public function uncomplete(): bool
    {
        $this->is_completed = false;
        $this->completed_at = null;
        
        return $this->save();
    }

    /**
     * Check if overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->is_completed) {
            return false;
        }
        
        return $this->due_date->isPast();
    }

    /**
     * Check if due today
     */
    public function isDueToday(): bool
    {
        return $this->due_date && $this->due_date->isToday();
    }

    /**
     * Scope for incomplete todos
     */
    public function scopeIncomplete($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope for completed todos
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope for overdue todos
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
                     ->whereDate('due_date', '<', today());
    }

    /**
     * Scope for due today
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    /**
     * Scope ordered by priority
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("CASE priority 
            WHEN 'urgent' THEN 1 
            WHEN 'high' THEN 2 
            WHEN 'normal' THEN 3 
            WHEN 'low' THEN 4 
            END");
    }

    /**
     * Get priority options
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }
}
