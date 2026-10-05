<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\AddSupplier;
use App\Models\SupplierWallet;
use App\Helpers\BookingPriceHelper;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    //Function for all supplier-payments
    public function index(Request $request) {
        //Base Query
        $query = Order::query()->select([
            'id',
            'item_id',
            'option_id',
            'type',
            'item_name',
            'start_date',
            'date',
            'qty_adult',
            'qty_child',
            'total_amount',
            'status',
            'payment_status',
            'currency',
            'supplier_name',
            'assign_supplier',
            'source',
            'booking_reference',
            'first_name',
            'last_name',
            'email',
            'phone_country_code',
            'phone',
        ]);
        //Search
        if ($request->filled('slw')) {
            $search = trim($request->slw);
            $number = preg_replace(
                '/[^0-9]/',
                '',
                $search
            );
            if ($number !== '') {
                $query->where(
                    'id',
                    $number
                );
            } else {
                $query->where(
                    'booking_reference',
                    $search
                );
            }
        }
        if ($request->filled('from_date')) {
            $query->whereDate(
                'date',
                '>=',
                $request->from_date
            );
        }
        if ($request->filled('to_date')) {
            $query->whereDate(
                'date',
                '<=',
                $request->to_date
            );
        }
        if ($request->filled('source')) {

            $query->where(
                'source',
                $request->source
            );
        }
        if ($request->filled('supplier')) {

            $query->where(
                'assign_supplier',
                $request->supplier
            );
        }
        if ($request->filled('payment_status')) {
            if (
                $request->payment_status === 'Paid'
            ) {
                $query->whereRaw(
                    'UPPER(status) = ?',
                    ['CONFIRMED']
                );
            } elseif (
                $request->payment_status === 'Unpaid'
            ) {
                $query->where(function ($q) {
                    $q->whereRaw(
                        'UPPER(status) = ?',
                        ['PENDING']
                    )
                    ->orWhereRaw(
                        'UPPER(status) = ?',
                        ['CANCEL']
                    )
                    ->orWhereRaw(
                        'UPPER(status) = ?',
                        ['CANCELLED']
                    );
                });
            } elseif (
                $request->payment_status === 'Partially Paid'
            ) {
                $query->whereRaw('1 = 0');
            }
        }
        //PAGINATION
        $orders = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();
        //SUPPLIERS
        $supplierIds = $orders
            ->getCollection()
            ->pluck('assign_supplier')
            ->filter()
            ->unique()
            ->values();
        $suppliers = collect();
        if ($supplierIds->isNotEmpty()) {
            $suppliers = AddSupplier::query()
                ->select([
                    'add_supplier_id',
                    'name'
                ])
                ->whereIn(
                    'add_supplier_id',
                    $supplierIds
                )
                ->get()
                ->keyBy('add_supplier_id');
        }
        //BOOKING DATA
        $bookings = $orders
            ->getCollection()
            ->map(function ($order) use ($suppliers) {
                $price = BookingPriceHelper::calculate(
                    $order
                );
                $net = (float) (
                    $price['net_amount'] ?? 0
                );

                $sell = (float) (
                    $price['sell_amount']
                    ?? $order->total_amount
                    ?? 0
                );
                $currency =
                    $price['currency']
                    ?? $order->currency
                    ?? 'THB';

                $status = strtoupper(
                    trim(
                        $order->status ?? ''
                    )
                );

               $status = strtoupper(
                    trim(
                        $order->status ?? ''
                    )
                );

                $paymentStatus = trim(
                    $order->payment_status ?? ''
                );

                $paymentStatus = $paymentStatus ?: 'Unpaid';

                $status = strtoupper(
                    trim(
                        $order->status ?? ''
                    )
                );

                $paymentStatus = trim(
                    $order->payment_status ?? ''
                );

                $paymentStatus = $paymentStatus ?: 'Unpaid';

                if (
                    strtolower(trim($paymentStatus)) === 'paid'
                ) {

                    $paid = $net;
                    $due = 0;

                } else {

                    $paid = 0;
                    $due = $net;
                }
                $supplier = null;

                if (
                    !empty($order->assign_supplier) &&
                    isset(
                        $suppliers[
                            $order->assign_supplier
                        ]
                    )
                ) {

                    $supplier =
                        $suppliers[
                            $order->assign_supplier
                        ]->name;
                }
                $supplier =
                    $supplier
                    ?: (
                        $order->supplier_name
                        ?: 'N/A'
                    );
                $customer = trim(
                    ($order->first_name ?? '') .
                    ' ' .
                    ($order->last_name ?? '')
                );

                $customer =
                    $customer ?: 'N/A';
                $phone = trim(
                    ($order->phone_country_code ?? '') .
                    ' ' .
                    ($order->phone ?? '')
                );

                return [
                    'id' =>
                        $order->id,
                    'slw_no' =>
                        'SLW' .
                        str_pad(
                            $order->id,
                            7,
                            '0',
                            STR_PAD_LEFT
                        ),
                    'type' =>
                        $order->type ?? 'N/A',
                    'product' =>
                        $order->item_name ?? 'N/A',
                    'travel_date' =>
                        $order->start_date,
                    'booking_date' =>
                        $order->date,
                    'customer' =>
                        $customer,
                    'phone' =>
                        $phone,
                    'email' =>
                        $order->email ?? 'N/A',
                    'source' =>
                        $order->source ?? 'N/A',
                    'supplier' =>
                        $supplier,
                    'adult' =>
                        (int) (
                            $price['adult']
                            ?? $order->qty_adult
                            ?? 0
                        ),
                    'child' =>
                        (int) (
                            $price['child']
                            ?? $order->qty_child
                            ?? 0
                        ),
                    'adult_price' =>
                        (float) (
                            $price['adult_net']
                            ?? 0
                        ),
                    'child_price' =>
                        (float) (
                            $price['child_net']
                            ?? 0
                        ),
                    'net' =>
                        $net,
                    'sell' =>
                        $sell,
                    'paid' =>
                        $paid,
                    'due' =>
                        $due,
                    'currency' =>
                        $currency,
                    'status' =>
                        $paymentStatus,
                        'payment_status' => $paymentStatus,
                    'order_status' =>
                        $status,
                ];
            })
            ->values();
        $totalNet =
            $bookings->sum('net');
        $totalSell =
            $bookings->sum('sell');
        $totalPaid =
            $bookings->sum('paid');
        $totalDue =
            $bookings->sum('due');
        $totalProfit =
            $totalSell -
            $totalNet;

        $sourceOptions = Order::query()
            ->whereNotNull('source')
            ->where(
                'source',
                '!=',
                ''
            )
            ->distinct()
            ->orderBy('source')
            ->pluck('source');

        return view('admin.finances.supplier-payments.supplier-payment', compact('bookings','orders','totalNet','totalSell','totalPaid','totalDue','totalProfit','sourceOptions','suppliers'));
    }

    //Function for update payment supplier
    public function update_payment(Request $request) {
        //Validate input fields
        $request->validate([
            'booking_id' => 'required|exists:orders,id',
            'payment_persons' => 'required|string|max:255',
            'paid_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:45',
            'transfer_amount' => 'nullable|numeric|min:0',
            'guide_amount' => 'nullable|numeric|min:0',
            'other_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'required|string|max:20',
            'transaction_reference' => 'required|string|max:45',
            'payment_date' => 'required|date',
            'payment_status' => 'required|string|max:50',
            'remark' => 'nullable|string',
        ]);

        $transfer = (float) ($request->transfer_amount ?? 0);
        $guide    = (float) ($request->guide_amount ?? 0);
        $other    = (float) ($request->other_amount ?? 0);

        //Total amount = Transfer + Guide + Other
        $totalAmount = $transfer + $guide + $other;

        $paid = $totalAmount;

        DB::beginTransaction();

        try {

            $order = Order::findOrFail(
                $request->booking_id
            );

            $price = BookingPriceHelper::calculate(
                $order
            );

            $sellAmount = (float) (
                $price['sell_amount']
                ?? $order->total_amount
                ?? 0
            );
            //Save Supplier Wallet
            SupplierWallet::create([
                'order_id'        => $order->id,
                'payment_person'  => $request->payment_persons,
                'recieved_amount' => $paid,
                'sell_amount'     => $sellAmount,
                'transaction_id'  => $request->transaction_reference,
                'transfer_amt'    => $transfer,
                'guide_amt'       => $guide,
                'other_amt'       => $other,
                'total_amt'       => $totalAmount,
                'payment_mode'    => $request->payment_mode,
                'travel_date'     => $request->payment_date,
                'supplier_id'     => $order->assign_supplier,
                'currency'        => $request->currency,
                'item_id'         => $order->item_id,
                'timestamp'       => now(),
            ]);
            //Update Order
            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'currency'          => $request->currency,
                    'payment_mode'      => $request->payment_mode,
                    'payment_status'    => $request->payment_status,
                    'status'    => $request->payment_status,
                    'paid_payment_date' => $request->payment_date,
                    'payment_remark'    => $request->remark,
                ]);

            DB::commit();
            return redirect()->route('admin.supplier.payments')->with('success', 'Supplier payment updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('unsuccess', $e->getMessage());
        }
    }
}