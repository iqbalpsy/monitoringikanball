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
        Schema::create('user_device_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // User yang diberi akses
            $table->foreignId('device_id')->constrained('devices'); // Device yang dapat diakses
            $table->foreignId('granted_by')->constrained('users'); // Admin yang memberikan akses
            $table->boolean('can_view_data')->default(true); // Izin melihat data
            $table->boolean('can_control')->default(false); // Izin kontrol (biasanya false untuk user)
            $table->timestamp('granted_at'); // Waktu akses diberikan
            $table->timestamp('expires_at')->nullable(); // Waktu akses berakhir (optional)
            $table->timestamps();
            
            // Unique constraint: satu user hanya punya satu record per device
            $table->unique(['user_id', 'device_id']);
            
            // Index untuk performa query
            $table->index(['user_id', 'can_view_data']);
            $table->index(['device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_device_access');
    }
};
