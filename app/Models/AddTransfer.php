<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddTransfer extends Model
{
    protected $table = 'add_transfer';

    protected $primaryKey = 'add_transfer_id';

    public $timestamps = false;

    protected $fillable = [
        'transfer_from',
        'transfer_name',
        'transfer_to',
        'transfer_type',
        'asign_supplier_car',
        'asign_supplier_van',
        'price_for_car',
        'car_net_price',
        'price_for_van',
        'van_net_price',
        'b2b_sell_price',
        'add_markup',
        'currency',
        'tour_of_title',
        'transfer_information',
        'announcement',
        'meta_keywords',
        'meta_description',
        'latitude',
        'longitude',
        'isactive',
        'supplier',
        'timestamp',
        'price_for_sedan',
        'sedan_net_price',
        'tour_category',
        'min_adult',
        'max_adult',
        'status',
        'adult',
        'forward_supplier_tour',
        'city',
        'country',
        'tr_type',
        'meeting_point',
        'create_date',
        'approved',
        'travel_time',
        'cancellation_policy',
    ];
}