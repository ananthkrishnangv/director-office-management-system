<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MeetingSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'director_id',
        'appointment_id',
        'created_by',
        'lab_id',
        'title',
        'description',
        'meeting_type',
        'start_time',
        'end_time',
        'is_all_day',
        'location',
        'online_link',
        'room_number',
        'attendees',
        'status',
        'notes',
        'outcome',
        'action_items',
        'google_event_id',
        'microsoft_event_id',
        'is_recurring',
        'recurrence_rule',
        'parent_meeting_id',
        'priority',
        'is_private',
        'color',
        'documents',
        'minutes',
        'scheduled_date',
        'visitor_name',
        'visitor_email',
        'visitor_phone',
        'visitor_organization',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'is_all_day' => 'boolean',
            'attendees' => 'array',
            'is_recurring' => 'boolean',
            'is_private' => 'boolean',
            'documents' => 'array',
            'scheduled_date' => 'date',
        ];
    }

    // Status constants
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_POSTPONED = 'postponed';

    // Meeting colors
    const COLORS = [
        'office_meeting' => '#0078d4',
        'boardroom' => '#107c10',
        'online' => '#8764b8',
        'site_visit' => '#ffb900',
        'lab_visit' => '#00b7c3',
        'external' => '#e74856',
        'internal' => '#5c2d91',
        'other' => '#737373',
    ];

    /**
     * Director who owns this meeting
     */
    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Appointment this meeting was created from
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * User who created this meeting
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Parent meeting for recurring meetings
     */
    public function parentMeeting()
    {
        return $this->belongsTo(MeetingSchedule::class, 'parent_meeting_id');
    }

    /**
     * Child meetings for recurring meetings
     */
    public function childMeetings()
    {
        return $this->hasMany(MeetingSchedule::class, 'parent_meeting_id');
    }

    /**
     * Related todos
     */
    public function todos()
    {
        return $this->hasMany(Todo::class, 'related_meeting_id');
    }

    /**
     * Get duration in minutes
     */
    public function getDurationMinutes(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Get formatted time range
     */
    public function getTimeRange(): string
    {
        return $this->start_time->format('h:i A') . ' - ' . $this->end_time->format('h:i A');
    }

    /**
     * Check if meeting is today
     */
    public function isToday(): bool
    {
        return $this->start_time->isToday();
    }

    /**
     * Check if meeting is ongoing
     */
    public function isOngoing(): bool
    {
        $now = Carbon::now();
        return $now->between($this->start_time, $this->end_time);
    }

    /**
     * Check if meeting is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture();
    }

    /**
     * Scope for today's meetings
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    /**
     * Scope for upcoming meetings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now())
            ->orderBy('start_time');
    }

    /**
     * Scope for a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }

    /**
     * Scope for a specific director
     */
    public function scopeForDirector($query, int $directorId)
    {
        return $query->where('director_id', $directorId);
    }

    /**
     * Scope for active meetings (not cancelled)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED]);
    }

    /**
     * Lab this meeting belongs to
     */
    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Scope to filter meetings by lab
     */
    public function scopeForLab($query, $labId)
    {
        return $query->where('lab_id', $labId);
    }

    /**
     * Get the appropriate color for this meeting type
     */
    public function getColorAttribute($value): string
    {
        return $value ?? (self::COLORS[$this->meeting_type] ?? '#0078d4');
    }

    /**
     * Format for calendar display
     */
    public function toCalendarEvent(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start_time->toIso8601String(),
            'end' => $this->end_time->toIso8601String(),
            'allDay' => $this->is_all_day,
            'color' => $this->color,
            'extendedProps' => [
                'meeting_type' => $this->meeting_type,
                'location' => $this->location,
                'status' => $this->status,
                'description' => $this->description,
            ],
        ];
    }
}
