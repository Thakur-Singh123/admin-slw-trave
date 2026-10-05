<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    //Call db
    protected $table = 'enquiry';

    protected $primaryKey = 'id';

    public $timestamps = false;

    //fillable field
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'from_date',
        'to_date',
        'adult',
        'child',
        'destination_1',
        'destination_2',
        'nights_1',
        'nights_2',
        'hotel_type',
        'food_type',
        'remark',
        'timestamp',
        'date',
        'enquiry_no',
        'ip',
        'status',
        'source',
        'lead_is_published',
        'lead_assigned_agent_id',
        'lead_claimed_at',
        'lead_converted_at',
        'lead_type',
    ];

    protected $casts = [
        'lead_is_published' => 'boolean',
        'lead_assigned_agent_id' => 'integer',
        'lead_claimed_at' => 'datetime',
        'lead_converted_at' => 'datetime',
    ];
}