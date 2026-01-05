<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyLogbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'director_id',
        'created_by',
        'log_date',
        'entries',
        'summary',
        'notes',
        'total_meetings',
        'total_visitors',
        'total_calls',
        'staff_met',
        'important_items',
        'pending_actions',
        'is_finalized',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'entries' => 'array',
            'staff_met' => 'array',
            'important_items' => 'array',
            'pending_actions' => 'array',
            'is_finalized' => 'boolean',
            'finalized_at' => 'datetime',
        ];
    }

    /**
     * Director who owns this logbook
     */
    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * User who created this logbook entry
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Add an entry to the logbook
     */
    public function addEntry(array $entry): bool
    {
        $entries = $this->entries ?? [];
        $entry['id'] = uniqid();
        $entry['timestamp'] = now()->toIso8601String();
        $entries[] = $entry;
        $this->entries = $entries;
        
        return $this->save();
    }

    /**
     * Finalize the logbook
     */
    public function finalize(): bool
    {
        $this->is_finalized = true;
        $this->finalized_at = now();
        
        return $this->save();
    }

    /**
     * Scope for a specific director
     */
    public function scopeForDirector($query, int $directorId)
    {
        return $query->where('director_id', $directorId);
    }

    /**
     * Scope for today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('log_date', today());
    }

    /**
     * Scope for a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('log_date', [$startDate, $endDate]);
    }

    /**
     * Get or create today's logbook for a director
     */
    public static function getOrCreateForToday(int $directorId, ?int $createdBy = null): self
    {
        return self::firstOrCreate(
            [
                'director_id' => $directorId,
                'log_date' => today(),
            ],
            [
                'created_by' => $createdBy,
                'entries' => [],
                'staff_met' => [],
                'important_items' => [],
                'pending_actions' => [],
            ]
        );
    }
}
