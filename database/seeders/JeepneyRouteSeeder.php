<?php

namespace Database\Seeders;

use App\Models\JeepneyRoute;
use Illuminate\Database\Seeder;

class JeepneyRouteSeeder extends Seeder
{
    public function run(): void
    {
        // 43 official Baguio City jeepney routes from
        // https://alternateroutes.baguio.gov.ph/jeepneyroutes/
        // Fares per LTFRB: Ordinary PHP 13.00 / Modernized PHP 14.00 (base)
        // Discounted (PWD/Student/Senior): Ordinary PHP 9.00 / Modernized PHP 11.20
        $routes = [
            ['name' => 'Pacdal Road - Claudio St',                          'origin' => 'Pacdal Road',                      'destination' => 'Claudio St'],
            ['name' => 'Upper P Burgos St. - Pinget',                       'origin' => 'Upper P Burgos St.',               'destination' => 'Pinget'],
            ['name' => 'Perfecto St. (Loakan Terminal) - Loakan Road',      'origin' => 'Perfecto St. (Loakan Terminal)',   'destination' => 'Loakan Road',     'vehicle_type' => 'modernized', 'fare_regular' => 14.00, 'fare_discounted' => 11.20],
            ['name' => 'Diego Silang St. (PNR Terminal) - PNR',             'origin' => 'Diego Silang St. (PNR Terminal)', 'destination' => 'PNR'],
            ['name' => 'Diego Silang St. (Hillside Terminal) - Hillside',   'origin' => 'Diego Silang St. (Hillside Terminal)', 'destination' => 'Hillside'],
            ['name' => 'Upper P Burgos St. - Pinget Road',                  'origin' => 'Upper P Burgos St.',               'destination' => 'Pinget Road'],
            ['name' => 'Tacay Road - Upper Kayang St',                      'origin' => 'Tacay Road',                       'destination' => 'Upper Kayang St'],
            ['name' => 'Diego Silang (Dagsian Terminal) - Dagsian',         'origin' => 'Diego Silang (Dagsian Terminal)', 'destination' => 'Dagsian'],
            ['name' => 'Quirino Highway - Shagem Street',                   'origin' => 'Quirino Highway',                  'destination' => 'Shagem Street'],
            ['name' => 'Cerantes St. (Gabriela Terminal) - Gabriela Silang','origin' => 'Cerantes St. (Gabriela Terminal)','destination' => 'Gabriela Silang'],
            ['name' => 'Kayang St. - Ferguson Road',                        'origin' => 'Kayang St.',                       'destination' => 'Ferguson Road'],
            ['name' => 'Mines View - Burnham Park',                         'origin' => 'Mines View',                       'destination' => 'Burnham Park'],
            ['name' => 'Shanum Street - Crystal Cave',                      'origin' => 'Shanum Street',                    'destination' => 'Crystal Cave'],
            ['name' => 'Manuel Roxas Road - Dagohoy St.',                   'origin' => 'Manuel Roxas Road',                'destination' => 'Dagohoy St.'],
            ['name' => 'Burnham - Stone Kingdom',                           'origin' => 'Burnham',                          'destination' => 'Stone Kingdom'],
            ['name' => 'Imelda Village Road - Dagohoy St.',                 'origin' => 'Imelda Village Road',              'destination' => 'Dagohoy St.'],
            ['name' => 'Calderon St. - Happy Hallows P-1',                  'origin' => 'Calderon St.',                     'destination' => 'Happy Hallows P-1'],
            ['name' => 'Calderon St. - Leonard Wood Road',                  'origin' => 'Calderon St.',                     'destination' => 'Leonard Wood Road'],
            ['name' => 'Upper Quirino Hill - Magsaysay Avenue',             'origin' => 'Upper Quirino Hill',               'destination' => 'Magsaysay Avenue'],
            ['name' => 'Carantes St. - Kennon Road',                        'origin' => 'Carantes St.',                     'destination' => 'Kennon Road'],
            ['name' => 'Crystal Cave Road - Otek Street',                   'origin' => 'Crystal Cave Road',                'destination' => 'Otek Street'],
            ['name' => 'Kayang St. - Extension Road',                       'origin' => 'Kayang St.',                       'destination' => 'Extension Road'],
            ['name' => 'Claudio St. - Leonard Wood Road',                   'origin' => 'Claudio St.',                      'destination' => 'Leonard Wood Road'],
            ['name' => 'Magsaysay Avenue - Harrison Road',                  'origin' => 'Magsaysay Avenue',                 'destination' => 'Harrison Road'],
            ['name' => 'Shugem St. - Palispis Highway',                     'origin' => 'Shugem St.',                       'destination' => 'Palispis Highway'],
            ['name' => 'Shagem St. - Green Lane',                           'origin' => 'Shagem St.',                       'destination' => 'Green Lane'],
            ['name' => 'San Carlos Heights Road - Kayang St.',              'origin' => 'San Carlos Heights Road',          'destination' => 'Kayang St.'],
            ['name' => 'Asin Road - Upper Kayang Street',                   'origin' => 'Asin Road',                        'destination' => 'Upper Kayang Street'],
            ['name' => 'Shagem Street - Upper Kayang Street',               'origin' => 'Shagem Street',                    'destination' => 'Upper Kayang Street'],
            ['name' => 'Lucnab - Veterans Loop',                            'origin' => 'Lucnab',                           'destination' => 'Veterans Loop'],
            ['name' => 'South Drive - Veterans Loop',                       'origin' => 'South Drive',                      'destination' => 'Veterans Loop'],
            ['name' => 'Rimando Road - Harrison Road',                      'origin' => 'Rimando Road',                     'destination' => 'Harrison Road'],
            ['name' => 'Upper Kayang St. - Avelino St.',                    'origin' => 'Upper Kayang St.',                 'destination' => 'Avelino St.'],
            ['name' => 'Everlasting St. - Otek St.',                        'origin' => 'Everlasting St.',                  'destination' => 'Otek St.'],
            ['name' => 'Bakakeng Road - Perfecto',                          'origin' => 'Bakakeng Road',                    'destination' => 'Perfecto'],
            ['name' => 'Harrison Road - Bakakeng Road',                     'origin' => 'Harrison Road',                    'destination' => 'Bakakeng Road'],
            ['name' => 'Palispis Highway - Otek Street',                    'origin' => 'Palispis Highway',                 'destination' => 'Otek Street'],
            ['name' => 'Gov. Pack - Perfecto Street',                       'origin' => 'Gov. Pack',                        'destination' => 'Perfecto Street'],
            ['name' => 'Loakan Road - Perfecto Street',                     'origin' => 'Loakan Road',                      'destination' => 'Perfecto Street',  'vehicle_type' => 'modernized', 'fare_regular' => 14.00, 'fare_discounted' => 11.20],
            ['name' => 'Harrison Road - UP Drive',                          'origin' => 'Harrison Road',                    'destination' => 'UP Drive'],
            ['name' => 'Quirino Highway (PHI-SCI) - Loakan Road (PEZA)',    'origin' => 'Quirino Highway (PHI-SCI)',        'destination' => 'Loakan Road (PEZA)', 'vehicle_type' => 'modernized', 'fare_regular' => 14.00, 'fare_discounted' => 11.20],
            ['name' => 'Bell Church - Loakan Road (PEZA)',                  'origin' => 'Bell Church',                      'destination' => 'Loakan Road (PEZA)', 'vehicle_type' => 'modernized', 'fare_regular' => 14.00, 'fare_discounted' => 11.20],
            ['name' => 'Tacay Road - Kayang St.',                           'origin' => 'Tacay Road',                       'destination' => 'Kayang St.'],
        ];

        foreach ($routes as $route) {
            JeepneyRoute::create([
                'name'                  => $route['name'],
                'origin'                => $route['origin'],
                'destination'           => $route['destination'],
                'fare_regular'          => $route['fare_regular']    ?? 13.00,
                'fare_discounted'       => $route['fare_discounted'] ?? 9.00,
                'vehicle_type'          => $route['vehicle_type']    ?? 'ordinary',
                'operating_hours_start' => '06:00:00',
                'operating_hours_end'   => '21:00:00',
            ]);
        }
    }
}
