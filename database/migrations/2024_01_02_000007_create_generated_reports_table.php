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
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('director_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Report Information
            $table->string('title');
            $table->enum('type', [
                'monthly',
                'weekly',
                'daily',
                'custom',
                'appointments',
                'meetings',
                'statistics'
            ])->default('monthly');
            
            // Period
            $table->date('period_start');
            $table->date('period_end');
            
            // File Information
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_format', 10)->default('pdf');
            $table->integer('file_size')->nullable();
            
            // Generation Status
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed'
            ])->default('pending');
            $table->text('error_message')->nullable();
            
            // Report Configuration (JSON)
            $table->json('config')->nullable();
            
            // Statistics stored for quick access
            $table->json('statistics')->nullable();
            
            // Download tracking
            $table->integer('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            
            // Scheduling
            $table->boolean('is_scheduled')->default(false);
            $table->string('schedule_frequency')->nullable();
            
            $table->timestamps();
            
            // Index for listing reports
            $table->index(['director_id', 'type']);
            $table->index(['director_id', 'period_start']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_reports');
    }
};
