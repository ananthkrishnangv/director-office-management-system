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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('director_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Requester Information
            $table->string('requester_name');
            $table->string('requester_email');
            $table->string('requester_phone')->nullable();
            $table->string('requester_organization')->nullable();
            $table->string('requester_designation')->nullable();
            
            // Appointment Details
            $table->string('purpose');
            $table->text('description')->nullable();
            $table->enum('meeting_type', [
                'office_meeting',
                'boardroom',
                'online',
                'site_visit',
                'lab_visit',
                'other'
            ])->default('office_meeting');
            
            // Scheduling
            $table->date('requested_date');
            $table->time('requested_start_time');
            $table->time('requested_end_time')->nullable();
            $table->date('approved_date')->nullable();
            $table->time('approved_start_time')->nullable();
            $table->time('approved_end_time')->nullable();
            $table->integer('duration_minutes')->default(30);
            
            // Status Workflow
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'completed',
                'cancelled',
                'rescheduled'
            ])->default('pending');
            
            // Admin Notes
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Notification Tracking
            $table->boolean('requester_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            
            // Source Tracking
            $table->enum('source', ['portal', 'phone', 'email', 'manual'])->default('portal');
            
            // Priority
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['director_id', 'status']);
            $table->index(['director_id', 'requested_date']);
            $table->index('status');
            $table->index('requester_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
