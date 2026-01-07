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
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->string('lab_code', 20)->unique(); // e.g., SERC, NAL, NCL
            $table->string('lab_name'); // e.g., CSIR-SERC
            $table->string('full_name'); // e.g., Structural Engineering Research Centre
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index('lab_code');
            $table->index('is_active');
        });

        // Add lab_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->onDelete('set null');
            $table->index('lab_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });
        
        Schema::dropIfExists('labs');
    }
};
