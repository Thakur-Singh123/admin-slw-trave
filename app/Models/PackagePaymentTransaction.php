<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagePaymentTransaction extends Model
{
    protected $table = 'package_payment_transactions';

    protected $fillable = [
        'package_finance_id',
        'transaction_type',
        'amount',
        'currency',
        'payment_mode',
        'transaction_reference',
        'payment_date',
        'receipt_path',
        'remark',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public $timestamps = false;
}