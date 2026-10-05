<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentWallet extends Model
{
    protected $table = 'agent_wallets';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'add_agent_id',
        'order_id',
        'deposit_date',
        'withdrawal_date',
        'deposit_amount',
        'withdrawal_amount',
        'gst',
        'total_amount',
        'account_name',
        'isactive',
        'cancel_amount',
        'payment_mathod',
        'payment_getway_type',
        'trasaction_number',
        'payment_option',
        'payment_status',
        'commets',
        'request_json',
        'response_json',
    ];

    protected $casts = [
        'deposit_date' => 'datetime',
        'withdrawal_date' => 'datetime',
        'deposit_amount' => 'float',
        'withdrawal_amount' => 'float',
        'gst' => 'float',
        'total_amount' => 'float',
        'isactive' => 'integer',
    ];
}