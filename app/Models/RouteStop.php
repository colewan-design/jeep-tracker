<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'jeepney_route_id',
        'name',
        'latitude',
        'longitude',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'float',
            'longitude' => 'float',
            'sequence'  => 'integer',
        ];
    }

    public function route()
    {
        return $this->belongsTo(JeepneyRoute::class, 'jeepney_route_id');
    }
}
