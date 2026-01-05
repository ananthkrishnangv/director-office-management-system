<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_enabled',
        'email_appointments',
        'email_meetings',
        'email_reminders',
        'email_daily_summary',
        'whatsapp_enabled',
        'whatsapp_appointments',
        'whatsapp_meetings',
        'whatsapp_reminders',
        'whatsapp_number',
        'in_app_enabled',
        'in_app_sound',
        'reminder_minutes_before',
        'reminder_times',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'daily_digest',
        'daily_digest_time',
        'weekly_digest',
        'weekly_digest_day',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
            'email_appointments' => 'boolean',
            'email_meetings' => 'boolean',
            'email_reminders' => 'boolean',
            'email_daily_summary' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'whatsapp_appointments' => 'boolean',
            'whatsapp_meetings' => 'boolean',
            'whatsapp_reminders' => 'boolean',
            'in_app_enabled' => 'boolean',
            'in_app_sound' => 'boolean',
            'reminder_times' => 'array',
            'quiet_hours_enabled' => 'boolean',
            'daily_digest' => 'boolean',
            'weekly_digest' => 'boolean',
        ];
    }

    /**
     * User who owns these preferences
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if currently in quiet hours
     */
    public function isInQuietHours(): bool
    {
        if (!$this->quiet_hours_enabled) {
            return false;
        }

        $now = now()->format('H:i:s');
        $start = $this->quiet_hours_start;
        $end = $this->quiet_hours_end;

        if ($start < $end) {
            return $now >= $start && $now <= $end;
        }
        
        // Handles overnight quiet hours (e.g., 22:00 to 06:00)
        return $now >= $start || $now <= $end;
    }

    /**
     * Should send email for appointment
     */
    public function shouldEmailAppointment(): bool
    {
        return $this->email_enabled && $this->email_appointments && !$this->isInQuietHours();
    }

    /**
     * Should send WhatsApp for appointment
     */
    public function shouldWhatsAppAppointment(): bool
    {
        return $this->whatsapp_enabled && $this->whatsapp_appointments && $this->whatsapp_number && !$this->isInQuietHours();
    }

    /**
     * Get or create preferences for a user
     */
    public static function getOrCreateForUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'email_enabled' => true,
                'email_appointments' => true,
                'email_meetings' => true,
                'email_reminders' => true,
                'whatsapp_enabled' => false,
                'in_app_enabled' => true,
                'in_app_sound' => true,
                'reminder_minutes_before' => 30,
            ]
        );
    }
}
