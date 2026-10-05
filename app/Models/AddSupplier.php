<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddSupplier extends Model
{
    //Call db table
    protected $table = 'add_supplier';

    //Fillable field
    protected $fillable = [
        'name',
        'contact_person',
        'address',
        'telephone',
        'fax',
        'email',
        'partial_payment_allow',
        'vat_invoice_allow',
        'password',
        'image',
        'licence',
        'isactive',
        'token',
        'country',
        'tour_location',
        'company_name',
        'type',
        'toins_user_id',
        'services',
    ];
}