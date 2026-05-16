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
        'distance_km',
        'route_points',
        'avg_speed',
        'max_speed',
        'passenger_count',
    ];

    protected $appends = ['duration_secs'];

    protected function casts(): array
    {
        return [
            'started_at'   => 'datetime',
            'ended_at'     => 'datetime',
            'route_points' => 'array',
            'avg_speed'    => 'float',
            'max_speed'    => 'float',
            'distance_km'  => 'float',
        ];
    }

    public function getDurationSecsAttribute(): ?int
    {
        if ($this->started_at && $this->ended_at) {
            return (int) $this->ended_at->diffInSeconds($this->started_at);
        }
        return null;
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
