<?php

namespace App\Models;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;

class Insurer extends Model
{
    protected $table = 'insurers';

    // protected $dateFormat = 'U';

    protected $casts = [
    ];

    public function getIdHashAttribute()
    {
        return Hashids::encode($this->id);
    }
}
