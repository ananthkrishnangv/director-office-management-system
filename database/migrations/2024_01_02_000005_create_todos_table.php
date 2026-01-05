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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Task Details
            $table->string('title');
            $table->text('description')->nullable();
            
            // Priority and Categorization
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            
            // Due Date
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            
            // Status
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            
            // Related Entities
            $table->foreignId('related_meeting_id')->nullable()->constrained('meeting_schedules')->onDelete('set null');
            $table->foreignId('related_appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            
            // Reminder
            $table->boolean('has_reminder')->default(false);
            $table->timestamp('reminder_at')->nullable();
            $table->boolean('reminder_sent')->default(false);
            
            // Ordering
            $table->integer('sort_order')->default(0);
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'is_completed']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
