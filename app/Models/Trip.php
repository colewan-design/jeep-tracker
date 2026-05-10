<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'jeep_id',
        'origin',
        'destination',
        'status',
        'started_at',
        'ended_at',
        'avg_speed',
        'max_speed',
        'route_points',
    ];

    protected function casts(): array
    {
        return [
            'started_at'   => 'datetime',
            'ended_at'     => 'datetime',
            'route_points' => 'array',
        ];
    }

    public function jeep()
    {
        return $this->belongsTo(Jeep::class);
    }
}
