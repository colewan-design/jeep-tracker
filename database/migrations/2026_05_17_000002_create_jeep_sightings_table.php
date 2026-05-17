<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jeep_sightings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jeepney_route_id')->constrained()->cascadeOnDelete();
            // Where the jeep was spotted
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            // Where the reporter was standing (for geofence verification)
            $table->decimal('reporter_latitude', 10, 7);
            $table->decimal('reporter_longitude', 10, 7);
            // Crowd voting
            $table->unsignedSmallInteger('confirmations')->default(1);
            $table->unsignedSmallInteger('denials')->default(0);
            // Auto-expire after 8 minutes with no confirmation
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jeep_sightings');
    }
};