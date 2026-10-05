<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddAgent extends Model
{
    //Call Db table
    protected $table = 'add_agent';
    protected $primaryKey = 'add_agent_id';
    public $incrementing = true;
    protected $keyType = 'int';
    //Fillable fields
    protected $fillable = [
        'name',
        'contact_person',
        'address',
        'telephone',
        'fax',
        'email',
        'password',
        'image',
        'logo_image',
        'incorporation_certificate',
        'gst_certificate',
        'company_profile',
        'username',
        'business_type',
        'position',
        'city',
        'state',
        'pincode',
        'country',
        'website',
        'current_xml',
        'additional_information',
        'isactive',
        'fcm_token',
        'timestamp',
        'payment_status',
        'order_id',
        'date',
        'last_login',
        'referral_code',
        'use_referral_code',
        'ip',
        'status',
        'source',
        'account_type',
        'remark',
        'doc_url',
        'gst_number',
        'gst_company',
        'IATA_status',
        'IATA_Code',
        'is_master',
    ];
}