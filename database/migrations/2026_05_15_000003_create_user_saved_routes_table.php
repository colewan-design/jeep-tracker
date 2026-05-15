<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_saved_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jeepney_route_id')->constrained()->cascadeOnDelete();
            $table->string('nickname')->nullable();          // User's custom label for the bookmark
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'jeepney_route_id']); // One bookmark per route per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_saved_routes');
    }
};
