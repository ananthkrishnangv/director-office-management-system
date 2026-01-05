<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entry_date',
        'title',
        'content',
        'tags',
        'mood',
        'category',
        'is_private',
        'attachments',
        'word_count',
        'is_draft',
        'last_auto_saved_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'tags' => 'array',
            'attachments' => 'array',
            'is_private' => 'boolean',
            'is_draft' => 'boolean',
            'last_auto_saved_at' => 'datetime',
        ];
    }

    /**
     * User who owns this entry
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Update word count before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->word_count = str_word_count(strip_tags($model->content ?? ''));
        });
    }

    /**
     * Publish the draft
     */
    public function publish(): bool
    {
        $this->is_draft = false;
        return $this->save();
    }

    /**
     * Auto-save the entry
     */
    public function autoSave(string $content): bool
    {
        $this->content = $content;
        $this->last_auto_saved_at = now();
        $this->is_draft = true;
        
        return $this->save();
    }

    /**
     * Scope for published entries
     */
    public function scopePublished($query)
    {
        return $query->where('is_draft', false);
    }

    /**
     * Scope for drafts
     */
    public function scopeDrafts($query)
    {
        return $query->where('is_draft', true);
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    /**
     * Search entries
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'ilike', "%{$term}%")
              ->orWhere('content', 'ilike', "%{$term}%");
        });
    }

    /**
     * Get or create today's entry for a user
     */
    public static function getOrCreateForToday(int $userId): self
    {
        return self::firstOrCreate(
            [
                'user_id' => $userId,
                'entry_date' => today(),
            ],
            [
                'content' => '',
                'is_draft' => true,
            ]
        );
    }
}
