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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Temperature thresholds
            $table->decimal('temp_min', 5, 2)->default(24.00);
            $table->decimal('temp_max', 5, 2)->default(30.00);
            
            // pH thresholds
            $table->decimal('ph_min', 4, 2)->default(6.50);
            $table->decimal('ph_max', 4, 2)->default(8.50);
            
            // Oxygen thresholds
            $table->decimal('oxygen_min', 4, 2)->default(5.00);
            $table->decimal('oxygen_max', 4, 2)->default(8.00);
            
            $table->timestamps();
            
            // Each user should have only one settings record
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
