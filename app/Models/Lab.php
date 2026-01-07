<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'lab_code',
        'lab_name',
        'full_name',
        'address',
        'city',
        'state',
        'pincode',
        'phone',
        'email',
        'website',
        'logo_path',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Users belonging to this lab
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the director of this lab
     */
    public function director()
    {
        return $this->users()->where('role', 'director')->first();
    }

    /**
     * Get the PA(s) of this lab
     */
    public function personalAssistants()
    {
        return $this->users()->where('role', 'pa');
    }

    /**
     * Appointments for this lab
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Meeting schedules for this lab
     */
    public function meetingSchedules()
    {
        return $this->hasMany(MeetingSchedule::class);
    }

    /**
     * Scope for active labs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get display name with code
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->lab_name} ({$this->lab_code})";
    }
}
