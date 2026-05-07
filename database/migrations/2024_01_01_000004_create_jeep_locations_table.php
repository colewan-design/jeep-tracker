<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jeep_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jeep_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('speed')->nullable()->comment('km/h');
            $table->float('heading')->nullable()->comment('degrees 0-360');
            $table->float('accuracy')->nullable()->comment('meters');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['jeep_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jeep_locations');
    }
};
