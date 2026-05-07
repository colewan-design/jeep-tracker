<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jeep extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'plate_number',
        'route_name',
        'capacity',
        'status',
    ];

    public function locations()
    {
        return $this->hasMany(JeepLocation::class);
    }

    public function latestLocation()
    {
        return $this->hasOne(JeepLocation::class)->latestOfMany('recorded_at');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
