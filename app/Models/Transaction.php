<?php

namespace App\Models;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    // protected $dateFormat = 'c';

    protected $casts = [
        'bidding_ends' => 'timestamp',
        'data'         => 'object',
    ];

    public function getIdHashAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function bids()
    {
        return $this->hasMany('App\Models\Bid');
    }
}
