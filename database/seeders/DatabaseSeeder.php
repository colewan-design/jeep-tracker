<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            JeepneyRouteSeeder::class,
            UserSeeder::class,
            JeepSeeder::class,
        ]);
    }
}
