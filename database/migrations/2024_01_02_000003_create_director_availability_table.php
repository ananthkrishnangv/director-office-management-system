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
        Schema::create('director_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('director_id')->constrained('users')->onDelete('cascade');
            
            // Day of Week (0 = Sunday, 6 = Saturday)
            $table->tinyInteger('day_of_week');
            
            // Time Slots
            $table->time('start_time');
            $table->time('end_time');
            
            // Slot Configuration
            $table->integer('slot_duration_minutes')->default(30);
            $table->integer('buffer_minutes')->default(15);
            $table->integer('max_appointments_per_slot')->default(1);
            
            // Availability Status
            $table->boolean('is_available')->default(true);
            
            // Booking Constraints
            $table->integer('min_advance_hours')->default(24);
            $table->integer('max_advance_days')->default(30);
            
            // Special Notes
            $table->string('notes')->nullable();
            
            $table->timestamps();
            
            // Composite unique constraint
            $table->unique(['director_id', 'day_of_week', 'start_time']);
            
            // Index for quick lookups
            $table->index(['director_id', 'is_available']);
        });
        
        // Create blocked dates table for holidays and leaves
        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('director_id')->constrained('users')->onDelete('cascade');
            $table->date('blocked_date');
            $table->string('reason')->nullable();
            $table->enum('type', ['holiday', 'leave', 'travel', 'other'])->default('other');
            $table->boolean('is_all_day')->default(true);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
            
            $table->unique(['director_id', 'blocked_date']);
            $table->index(['director_id', 'blocked_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_dates');
        Schema::dropIfExists('director_availability');
    }
};
