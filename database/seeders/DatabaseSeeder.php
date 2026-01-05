<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DirectorAvailability;
use App\Models\NotificationPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Director
        $director = User::create([
            'name' => 'Director',
            'email' => 'director@serc.res.in',
            'password' => Hash::make('password'),
            'role' => 'director',
            'department' => 'Director Office',
            'designation' => 'Director',
            'phone' => '+91-9876543210',
            'is_active' => true,
        ]);

        // Create PA
        User::create([
            'name' => 'Personal Assistant',
            'email' => 'pa@serc.res.in',
            'password' => Hash::make('password'),
            'role' => 'pa',
            'department' => 'Director Office',
            'designation' => 'Personal Assistant',
            'is_active' => true,
        ]);

        // Create Admin
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@serc.res.in',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department' => 'IT',
            'designation' => 'System Admin',
            'is_active' => true,
        ]);

        // Create availability for director (Mon-Fri, 10:00-17:00)
        foreach ([1, 2, 3, 4, 5] as $day) {
            DirectorAvailability::create([
                'director_id' => $director->id,
                'day_of_week' => $day,
                'start_time' => '10:00',
                'end_time' => '13:00',
                'slot_duration_minutes' => 30,
                'buffer_minutes' => 10,
                'is_available' => true,
            ]);
            DirectorAvailability::create([
                'director_id' => $director->id,
                'day_of_week' => $day,
                'start_time' => '14:00',
                'end_time' => '17:00',
                'slot_duration_minutes' => 30,
                'buffer_minutes' => 10,
                'is_available' => true,
            ]);
        }

        // Create notification preferences
        NotificationPreference::create([
            'user_id' => $director->id,
            'email_enabled' => true,
            'email_appointments' => true,
            'reminder_minutes_before' => 30,
        ]);
    }
}
