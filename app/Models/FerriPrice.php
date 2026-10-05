<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FerriPrice extends Model
{
    protected $table = 'ferri_prices';

    protected $primaryKey = 'price_id';

    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'ferry_id',
        'ferri_class',
        'net_price',
        'sell_price',
        'currency',
    ];
}