<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Email Notifications
            $table->boolean('email_enabled')->default(true);
            $table->boolean('email_appointments')->default(true);
            $table->boolean('email_meetings')->default(true);
            $table->boolean('email_reminders')->default(true);
            $table->boolean('email_daily_summary')->default(false);
            
            // WhatsApp Notifications
            $table->boolean('whatsapp_enabled')->default(false);
            $table->boolean('whatsapp_appointments')->default(true);
            $table->boolean('whatsapp_meetings')->default(true);
            $table->boolean('whatsapp_reminders')->default(true);
            $table->string('whatsapp_number')->nullable();
            
            // In-App Notifications
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('in_app_sound')->default(true);
            
            // Reminder Timing
            $table->integer('reminder_minutes_before')->default(30);
            $table->json('reminder_times')->nullable(); // Array of times for multiple reminders
            
            // Quiet Hours (Do Not Disturb)
            $table->boolean('quiet_hours_enabled')->default(false);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            
            // Digest Options
            $table->boolean('daily_digest')->default(false);
            $table->time('daily_digest_time')->nullable();
            $table->boolean('weekly_digest')->default(false);
            $table->tinyInteger('weekly_digest_day')->nullable(); // 0-6 for day of week
            
            $table->timestamps();
            
            // Unique constraint - one preference per user
            $table->unique('user_id');
        });
        
        // Create notifications log table
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->enum('channel', ['email', 'whatsapp', 'in_app', 'sms'])->default('email');
            $table->string('type');
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            
            $table->string('related_type')->nullable(); // appointments, meetings, etc.
            $table->unsignedBigInteger('related_id')->nullable();
            
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'channel']);
            $table->index(['user_id', 'status']);
            $table->index(['related_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_preferences');
    }
};
