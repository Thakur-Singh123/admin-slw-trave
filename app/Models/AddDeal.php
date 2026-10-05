<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddDeal extends Model
{
    //Call db
    protected $table = 'add_deal';

    protected $primaryKey = 'add_deal_id';

    public $timestamps = false;

    //Protected fillable
    protected $fillable = [
        'item_id',
        'item_type',
        'dealtitle',
        'discount',
        'validupto',
        'status',
    ];
}