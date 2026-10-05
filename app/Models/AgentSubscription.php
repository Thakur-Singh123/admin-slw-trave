<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentSubscription extends Model
{
    //Call db table
    protected $table = 'agent_subscription';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    //fillable fields
    protected $fillable = [
        'subscription_amt',
        'currency',
        'start_date',
        'end_date',
        'plan_type',
        'agent_id',
        'trasaction_id',
        'payment_option',
        'status',
        'lead_access',
    ];

    protected $casts = [
        'subscription_amt' => 'float',
        'start_date'      => 'date',
        'end_date'        => 'date',
        'agent_id'        => 'integer',
        'status'          => 'integer',
        'lead_access'     => 'boolean',
    ];

    //Function for get agent
    public function agent() {
        return $this->belongsTo(AddAgent::class, 'agent_id', 'add_agent_id');
    }

    //Function for payments
    public function payments() {
        return $this->hasMany(AgentLeadSubscriptionPayment::class, 'subscription_id', 'id');
    }
}