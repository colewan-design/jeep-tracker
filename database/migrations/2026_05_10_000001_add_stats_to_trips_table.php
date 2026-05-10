<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->float('avg_speed')->nullable()->after('ended_at');
            $table->float('max_speed')->nullable()->after('avg_speed');
            $table->json('route_points')->nullable()->after('max_speed');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['avg_speed', 'max_speed', 'route_points']);
        });
    }
};
