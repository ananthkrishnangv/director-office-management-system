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
        Schema::create('meeting_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('director_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Meeting Details
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('meeting_type', [
                'office_meeting',
                'boardroom',
                'online',
                'site_visit',
                'lab_visit',
                'external',
                'internal',
                'other'
            ])->default('office_meeting');
            
            // Scheduling
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('is_all_day')->default(false);
            
            // Location
            $table->string('location')->nullable();
            $table->string('online_link')->nullable();
            $table->string('room_number')->nullable();
            
            // Attendees (JSON array)
            $table->json('attendees')->nullable();
            
            // Meeting Status
            $table->enum('status', [
                'scheduled',
                'in_progress',
                'completed',
                'cancelled',
                'postponed'
            ])->default('scheduled');
            
            // Notes and Outcomes
            $table->text('notes')->nullable();
            $table->text('outcome')->nullable();
            $table->text('action_items')->nullable();
            
            // Calendar Integration
            $table->string('google_event_id')->nullable();
            $table->string('microsoft_event_id')->nullable();
            
            // Recurring Meeting
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule')->nullable();
            $table->foreignId('parent_meeting_id')->nullable()->constrained('meeting_schedules')->onDelete('cascade');
            
            // Priority and Visibility
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_private')->default(false);
            
            // Color for Calendar Display
            $table->string('color', 7)->default('#0078d4');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['director_id', 'start_time']);
            $table->index(['director_id', 'status']);
            $table->index('start_time');
            $table->index('google_event_id');
            $table->index('microsoft_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_schedules');
    }
};
