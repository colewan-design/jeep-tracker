<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jeepney_route_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedSmallInteger('sequence');    // Order along the route
            $table->timestamp('created_at')->useCurrent();

            $table->index(['jeepney_route_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stops');
    }
};
