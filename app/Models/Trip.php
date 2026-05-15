<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'jeep_id',
        'jeepney_route_id',
        'origin',
        'destination',
        'status',
        'started_at',
        'ended_at',
        'route_points',
        'avg_speed',
        'max_speed',
        'passenger_count',
    ];

    protected function casts(): array
    {
        return [
            'started_at'   => 'datetime',
            'ended_at'     => 'datetime',
            'route_points' => 'array',
            'avg_speed'    => 'float',
            'max_speed'    => 'float',
        ];
    }

    public function jeep()
    {
        return $this->belongsTo(Jeep::class);
    }

    public function jeepneyRoute()
    {
        return $this->belongsTo(JeepneyRoute::class);
    }
}
