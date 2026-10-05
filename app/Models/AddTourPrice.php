<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddTourPrice extends Model
{
    protected $table = 'add_tour_price';

    //Call db
    protected $primaryKey = 'add_tour_price_id';

    public $timestamps = false;

    //Fillable fields
    protected $fillable = [
        'add_tour_id',
        'add_rate_from',
        'add_rate_to',
        'min_qyt',
        'max_qyt',
        'net_price',
        'net_price_b2b',
        'add_markup',
        'sell_price',
        'type',
        'currency',
        'sub_supplier_id',
        'option_id',
        'last_update',
    ];
}