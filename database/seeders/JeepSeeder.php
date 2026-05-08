<?php

namespace Database\Seeders;

use App\Models\Jeep;
use App\Models\JeepLocation;
use Illuminate\Database\Seeder;

class JeepSeeder extends Seeder
{
    public function run(): void
    {
        $jeeps = [
            // Route 1: Happy Hollow - Town (Session Road)
            [
                'name'         => 'Happy Hollow 01',
                'plate_number' => 'BAG-1001',
                'route_name'   => 'Happy Hollow - Town',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4090,
                'lng'          => 120.5930,
            ],
            [
                'name'         => 'Happy Hollow 02',
                'plate_number' => 'BAG-1002',
                'route_name'   => 'Happy Hollow - Town',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4120,
                'lng'          => 120.5950,
            ],

            // Route 2: Aurora Hill - Town
            [
                'name'         => 'Aurora Hill 01',
                'plate_number' => 'BAG-2001',
                'route_name'   => 'Aurora Hill - Town',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4220,
                'lng'          => 120.6010,
            ],
            [
                'name'         => 'Aurora Hill 02',
                'plate_number' => 'BAG-2002',
                'route_name'   => 'Aurora Hill - Town',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4200,
                'lng'          => 120.5990,
            ],

            // Route 3: Trancoville - Town
            [
                'name'         => 'Trancoville 01',
                'plate_number' => 'BAG-3001',
                'route_name'   => 'Trancoville - Town',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4180,
                'lng'          => 120.6080,
            ],
            [
                'name'         => 'Trancoville 02',
                'plate_number' => 'BAG-3002',
                'route_name'   => 'Trancoville - Town',
                'capacity'     => 20,
                'status'       => 'inactive',
                'lat'          => 16.4160,
                'lng'          => 120.6060,
            ],

            // Route 4: Mines View - Baguio Plaza
            [
                'name'         => 'Mines View 01',
                'plate_number' => 'BAG-4001',
                'route_name'   => 'Mines View - Baguio Plaza',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4102,
                'lng'          => 120.6320,
            ],
            [
                'name'         => 'Mines View 02',
                'plate_number' => 'BAG-4002',
                'route_name'   => 'Mines View - Baguio Plaza',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4115,
                'lng'          => 120.6300,
            ],

            // Route 5: Loakan - Town
            [
                'name'         => 'Loakan 01',
                'plate_number' => 'BAG-5001',
                'route_name'   => 'Loakan - Town',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.3750,
                'lng'          => 120.5690,
            ],
            [
                'name'         => 'Loakan 02',
                'plate_number' => 'BAG-5002',
                'route_name'   => 'Loakan - Town',
                'capacity'     => 20,
                'status'       => 'maintenance',
                'lat'          => 16.3770,
                'lng'          => 120.5710,
            ],

            // Route 6: La Trinidad - Town (via Magsaysay)
            [
                'name'         => 'La Trinidad 01',
                'plate_number' => 'BAG-6001',
                'route_name'   => 'La Trinidad - Town',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4600,
                'lng'          => 120.5880,
            ],
            [
                'name'         => 'La Trinidad 02',
                'plate_number' => 'BAG-6002',
                'route_name'   => 'La Trinidad - Town',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4580,
                'lng'          => 120.5860,
            ],

            // Route 7: Marcos Highway - SM City
            [
                'name'         => 'Marcos Highway 01',
                'plate_number' => 'BAG-7001',
                'route_name'   => 'Marcos Highway - SM City',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4350,
                'lng'          => 120.6150,
            ],

            // Route 8: Engineers Hill - Town
            [
                'name'         => 'Engineers Hill 01',
                'plate_number' => 'BAG-8001',
                'route_name'   => 'Engineers Hill - Town',
                'capacity'     => 20,
                'status'       => 'active',
                'lat'          => 16.4280,
                'lng'          => 120.5980,
            ],
        ];

        foreach ($jeeps as $data) {
            $jeep = Jeep::create([
                'name'         => $data['name'],
                'plate_number' => $data['plate_number'],
                'route_name'   => $data['route_name'],
                'capacity'     => $data['capacity'],
                'status'       => $data['status'],
            ]);

            // Seed an initial location for each jeep
            JeepLocation::create([
                'jeep_id'     => $jeep->id,
                'latitude'    => $data['lat'],
                'longitude'   => $data['lng'],
                'speed'       => rand(5, 30),
                'heading'     => rand(0, 360),
                'accuracy'    => 10,
                'recorded_at' => now(),
            ]);
        }
    }
}
