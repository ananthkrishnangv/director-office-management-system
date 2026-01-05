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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Entry Date
            $table->date('entry_date');
            
            // Title (optional)
            $table->string('title')->nullable();
            
            // Content (Rich text)
            $table->longText('content');
            
            // Tags for organization
            $table->json('tags')->nullable();
            
            // Mood/Category tracking
            $table->string('mood')->nullable();
            $table->string('category')->nullable();
            
            // Privacy
            $table->boolean('is_private')->default(true);
            
            // Attachments (JSON array of file paths)
            $table->json('attachments')->nullable();
            
            // Word count for statistics
            $table->integer('word_count')->default(0);
            
            // Auto-save tracking
            $table->boolean('is_draft')->default(true);
            $table->timestamp('last_auto_saved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for search and filtering
            $table->index(['user_id', 'entry_date']);
            $table->index(['user_id', 'is_draft']);
            $table->fullText(['title', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
