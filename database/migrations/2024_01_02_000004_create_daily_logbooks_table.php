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
        Schema::create('daily_logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('director_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Date
            $table->date('log_date');
            
            // Entries (JSON array of activities)
            $table->json('entries')->nullable();
            
            // Summary
            $table->text('summary')->nullable();
            
            // Quick Notes
            $table->text('notes')->nullable();
            
            // Metadata
            $table->integer('total_meetings')->default(0);
            $table->integer('total_visitors')->default(0);
            $table->integer('total_calls')->default(0);
            
            // Staff Members Met (JSON array)
            $table->json('staff_met')->nullable();
            
            // Important Items (JSON array)
            $table->json('important_items')->nullable();
            
            // Pending Actions
            $table->json('pending_actions')->nullable();
            
            // Status
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            
            $table->timestamps();
            
            // Unique constraint for one log per day per director
            $table->unique(['director_id', 'log_date']);
            
            // Index for date-based queries
            $table->index(['director_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_logbooks');
    }
};
