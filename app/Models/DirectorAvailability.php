<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectorAvailability extends Model
{
    use HasFactory;

    protected $table = 'director_availability';

    protected $fillable = [
        'director_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration_minutes',
        'buffer_minutes',
        'max_appointments_per_slot',
        'is_available',
        'min_advance_hours',
        'max_advance_days',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'is_available' => 'boolean',
        ];
    }

    const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    /**
     * Director who owns this availability rule
     */
    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Get day name
     */
    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Check if this rule is for today
     */
    public function isForToday(): bool
    {
        return $this->day_of_week === now()->dayOfWeek;
    }

    /**
     * Scope for available days
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope for a specific director
     */
    public function scopeForDirector($query, int $directorId)
    {
        return $query->where('director_id', $directorId);
    }

    /**
     * Scope for a specific day
     */
    public function scopeForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }
}
