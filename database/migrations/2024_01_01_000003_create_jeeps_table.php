<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jeeps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('plate_number')->unique();
            $table->string('route_name')->nullable();
            $table->unsignedInteger('capacity')->default(16);
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jeeps');
    }
};
