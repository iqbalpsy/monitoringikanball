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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama device (ex: "Kolam A", "Tank 1")
            $table->string('device_id')->unique(); // ID unik device dari hardware
            $table->string('location')->nullable(); // Lokasi fisik device
            $table->text('description')->nullable(); // Deskripsi device
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline');
            $table->json('settings')->nullable(); // Setting device dalam JSON
            $table->foreignId('created_by')->constrained('users'); // Admin yang menambahkan
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable(); // Terakhir device mengirim data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
