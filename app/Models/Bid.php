<?php

namespace App\Models;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $table = 'bids';

    // protected $dateFormat = 'c';

    protected $casts = [
        'insurer_id'    => 'integer',
        'offer_price'   => 'integer',
        'round_expires' => 'date',
    ];

    public function getIdHashAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function transaction()
    {
        return $this->belongsTo('App\Models\Transaction');
    }

    public function round()
    {
        return $this->belongsTo('App\Models\Round');
    }

    public function insurer()
    {
        return $this->belongsTo('App\Models\Insurer');
    }
}
