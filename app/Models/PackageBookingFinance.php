<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageBookingFinance extends Model
{
    protected $table = 'package_booking_finance';

    protected $fillable = [
        'booking_reference',
        'package_name',
        'country',
        'booking_date',
        'travel_from',
        'travel_to',
        'booking_status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'adult_pax',
        'child_pax',
        'infant_pax',
        'currency',
        'sale_amount',
        'received_amount',
        'customer_due',
        'customer_payment_status',
        'hotel_cost',
        'transport_cost',
        'tour_cost',
        'guide_cost',
        'visa_cost',
        'other_package_cost',
        'total_package_cost',
        'supplier_name',
        'supplier_paid',
        'supplier_due',
        'supplier_payment_status',
        'agent_commission',
        'other_expense',
        'net_profit',
        'profit_margin',
        'payment_mode',
        'transaction_reference',
        'payment_date',
        'payment_receipt',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'travel_from' => 'date',
        'travel_to' => 'date',
        'payment_date' => 'date',
        'adult_pax' => 'integer',
        'child_pax' => 'integer',
        'infant_pax' => 'integer',
        'sale_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'customer_due' => 'decimal:2',
        'hotel_cost' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'tour_cost' => 'decimal:2',
        'guide_cost' => 'decimal:2',
        'visa_cost' => 'decimal:2',
        'other_package_cost' => 'decimal:2',
        'total_package_cost' => 'decimal:2',
        'supplier_paid' => 'decimal:2',
        'supplier_due' => 'decimal:2',
        'agent_commission' => 'decimal:2',
        'other_expense' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'profit_margin' => 'decimal:4',
    ];

    public function transactions() {
        return $this->hasMany(PackagePaymentTransaction::class, 'package_finance_id');
    }
}
