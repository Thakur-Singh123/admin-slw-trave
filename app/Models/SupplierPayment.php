<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $table = 'supplier_payments';

    protected $fillable = [
        'supplier_id',
        'amount',
        'order_id',
        'payment_date',
        'transaction_id',
        'payment_method',
        'type',
        'notes',
        'created_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'created_at' => 'datetime',
    ];
}