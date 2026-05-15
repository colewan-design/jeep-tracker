<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the status enum to include driver-facing trip states.
        // 'ongoing' is kept for backwards compatibility with existing records.
        DB::statement("ALTER TABLE trips MODIFY COLUMN status
            ENUM('waiting','boarding','in_transit','ongoing','completed','cancelled')
            NOT NULL DEFAULT 'waiting'");

        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'jeepney_route_id')) {
                $table->foreignId('jeepney_route_id')
                      ->nullable()
                      ->after('jeep_id')
                      ->constrained()
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('trips', 'route_points')) {
                $table->json('route_points')->nullable()->after('ended_at');
            }
            if (!Schema::hasColumn('trips', 'avg_speed')) {
                $table->float('avg_speed')->nullable()->after('route_points');
            }
            if (!Schema::hasColumn('trips', 'max_speed')) {
                $table->float('max_speed')->nullable()->after('avg_speed');
            }
            if (!Schema::hasColumn('trips', 'passenger_count')) {
                $table->unsignedSmallInteger('passenger_count')->nullable()->after('max_speed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jeepney_route_id');
            $table->dropColumn(['route_points', 'avg_speed', 'max_speed', 'passenger_count']);
        });

        DB::statement("ALTER TABLE trips MODIFY COLUMN status
            ENUM('ongoing','completed','cancelled')
            NOT NULL DEFAULT 'ongoing'");
    }
};
