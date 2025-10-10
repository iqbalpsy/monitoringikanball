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
        Schema::create('device_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices'); // Device yang dikontrol
            $table->foreignId('user_id')->constrained('users'); // Admin yang melakukan kontrol
            $table->string('action'); // Jenis aksi (ex: 'turn_on_pump', 'adjust_ph', 'emergency_stop')
            $table->json('parameters')->nullable(); // Parameter kontrol dalam JSON
            $table->json('previous_state')->nullable(); // State sebelum kontrol
            $table->json('new_state')->nullable(); // State setelah kontrol
            $table->enum('status', ['pending', 'executed', 'failed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable(); // Catatan dari admin
            $table->timestamp('executed_at')->nullable(); // Waktu eksekusi
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['device_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_controls');
    }
};
