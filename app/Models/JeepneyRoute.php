<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JeepneyRoute extends Model
{
    protected $fillable = [
        'name',
        'origin',
        'destination',
        'fare_regular',
        'fare_discounted',
        'vehicle_type',
        'operating_hours_start',
        'operating_hours_end',
        'polyline',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fare_regular'    => 'float',
            'fare_discounted' => 'float',
            'is_active'       => 'boolean',
        ];
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class)->orderBy('sequence');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function savedByUsers()
    {
        return $this->hasMany(UserSavedRoute::class);
    }

    /** Active jeeps currently running this route. */
    public function activeJeeps()
    {
        return $this->hasManyThrough(Jeep::class, Trip::class, 'jeepney_route_id', 'id', 'id', 'jeep_id')
                    ->where('jeeps.status', 'active')
                    ->where('trips.status', 'in_transit');
    }
}
