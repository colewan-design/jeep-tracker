<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@jeeptracker.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        // Sample drivers
        $drivers = [
            ['name' => 'Juan dela Cruz',    'email' => 'juan@jeeptracker.com'],
            ['name' => 'Pedro Santos',      'email' => 'pedro@jeeptracker.com'],
            ['name' => 'Mario Reyes',       'email' => 'mario@jeeptracker.com'],
            ['name' => 'Jose Bautista',     'email' => 'jose@jeeptracker.com'],
            ['name' => 'Ramon Garcia',      'email' => 'ramon@jeeptracker.com'],
            ['name' => 'Antonio Villanueva','email' => 'antonio@jeeptracker.com'],
            ['name' => 'Eduardo Flores',    'email' => 'eduardo@jeeptracker.com'],
            ['name' => 'Roberto Cruz',      'email' => 'roberto@jeeptracker.com'],
        ];

        foreach ($drivers as $driver) {
            User::create([
                'name'     => $driver['name'],
                'email'    => $driver['email'],
                'password' => Hash::make('password123'),
                'role'     => 'driver',
            ]);
        }

        // Sample passenger
        User::create([
            'name'     => 'Sample Passenger',
            'email'    => 'passenger@jeeptracker.com',
            'password' => Hash::make('password123'),
            'role'     => 'passenger',
        ]);
    }
}
