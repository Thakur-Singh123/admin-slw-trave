<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierWallet extends Model
{
    protected $table = 'supplier_wallets';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'payment_person',
        'recieved_amount',
        'sell_amount',
        'transaction_id',
        'transfer_amt',
        'guide_amt',
        'other_amt',
        'total_amt',
        'payment_mode',
        'travel_date',
        'supplier_id',
        'currency',
        'item_id',
        'timestamp',
    ];
}