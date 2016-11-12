<?php

namespace App\Models;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    protected $table = 'rounds';

    protected $casts = [
        'expires' => 'date',
        'closed'  => 'boolean',
    ];

    public function bids()
    {
        return $this->hasMany('App\Models\Bid');
    }
}
