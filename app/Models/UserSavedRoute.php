<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSavedRoute extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'jeepney_route_id',
        'nickname',
        'sort_order',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function route()
    {
        return $this->belongsTo(JeepneyRoute::class, 'jeepney_route_id');
    }
}
