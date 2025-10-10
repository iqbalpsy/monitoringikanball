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
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices'); // Device yang mengirim data
            $table->decimal('ph_level', 4, 2)->nullable(); // pH air (0.00 - 14.00)
            $table->decimal('temperature', 5, 2)->nullable(); // Suhu air dalam Celsius
            $table->decimal('oxygen_level', 5, 2)->nullable(); // Level oksigen dalam mg/L atau %
            $table->decimal('turbidity', 5, 2)->nullable(); // Kekeruhan air (optional)
            $table->json('raw_data')->nullable(); // Data mentah dari sensor dalam JSON
            $table->timestamp('recorded_at'); // Waktu data direkam di device
            $table->timestamps(); // created_at untuk waktu diterima server
            
            // Index untuk performa query
            $table->index(['device_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};
