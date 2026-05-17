<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JeepSighting extends Model
{
    protected $fillable = [
        'jeepney_route_id',
        'latitude',
        'longitude',
        'reporter_latitude',
        'reporter_longitude',
        'confirmations',
        'denials',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude'          => 'float',
            'longitude'         => 'float',
            'reporter_latitude' => 'float',
            'reporter_longitude'=> 'float',
            'expires_at'        => 'datetime',
        ];
    }

    public function jeepneyRoute()
    {
        return $this->belongsTo(JeepneyRoute::class);
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())
                     ->whereRaw('denials < confirmations * 2');
    }
}