<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jeeps', function (Blueprint $table) {
            $table->enum('seats_available', ['available', 'limited', 'full'])
                  ->default('available')
                  ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('jeeps', function (Blueprint $table) {
            $table->dropColumn('seats_available');
        });
    }
};