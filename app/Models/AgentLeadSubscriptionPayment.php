<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentLeadSubscriptionPayment extends Model
{
    protected $table = 'agent_lead_subscription_payments';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'agent_id',
        'subscription_id',
        'receipt',
        'razorpay_order_id',
        'razorpay_payment_id',
        'amount',
        'currency',
        'status',
        'failure_reason',
        'response_json',
        'paid_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'agent_id'        => 'integer',
        'subscription_id' => 'integer',
        'amount'          => 'integer',
        'paid_at'         => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    //Function for get subscription
    public function subscription() {
        return $this->belongsTo(AgentSubscription::class, 'subscription_id', 'id');
    }

    //Function for get agent
    public function agent() {
        return $this->belongsTo(AddAgent::class,'agent_id', 'add_agent_id');
    }
}