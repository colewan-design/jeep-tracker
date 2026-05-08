<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JeepLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'jeep_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'accuracy',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude'    => 'float',
            'longitude'   => 'float',
            'speed'       => 'float',
            'heading'     => 'float',
            'accuracy'    => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    public function jeep()
    {
        return $this->belongsTo(Jeep::class);
    }
}
