<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'director_id',
        'blocked_date',
        'reason',
        'type',
        'is_all_day',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'blocked_date' => 'date',
            'is_all_day' => 'boolean',
        ];
    }

    const TYPE_HOLIDAY = 'holiday';
    const TYPE_LEAVE = 'leave';
    const TYPE_TRAVEL = 'travel';
    const TYPE_OTHER = 'other';

    /**
     * Director who owns this blocked date
     */
    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Scope for a specific director
     */
    public function scopeForDirector($query, int $directorId)
    {
        return $query->where('director_id', $directorId);
    }

    /**
     * Scope for a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('blocked_date', [$startDate, $endDate]);
    }

    /**
     * Check if a specific date is blocked
     */
    public static function isDateBlocked(int $directorId, $date): bool
    {
        return self::where('director_id', $directorId)
                   ->whereDate('blocked_date', $date)
                   ->exists();
    }
}
