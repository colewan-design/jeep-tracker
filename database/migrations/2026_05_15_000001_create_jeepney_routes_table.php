<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jeepney_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                      // "Pacdal Road - Claudio St"
            $table->string('origin');
            $table->string('destination');
            $table->decimal('fare_regular', 6, 2)->default(13.00);
            $table->decimal('fare_discounted', 6, 2)->default(9.00);    // PWD/Student/Senior
            $table->enum('vehicle_type', ['modernized', 'ordinary'])->default('ordinary');
            $table->time('operating_hours_start')->default('06:00:00');
            $table->time('operating_hours_end')->default('21:00:00');
            $table->text('polyline')->nullable();                        // Encoded route path — null until first driver trip
            $table->boolean('is_active')->default(false);               // True when at least one jeep is live on this route
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jeepney_routes');
    }
};
