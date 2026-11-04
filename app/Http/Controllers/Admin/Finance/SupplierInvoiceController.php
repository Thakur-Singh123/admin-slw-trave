<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\AddSupplier;
use App\Models\SupplierWallet;
use App\Helpers\BookingPriceHelper;
use Carbon\Carbon;

class SupplierInvoiceController extends Controller
{
    
    //Function for show invoice
    public function show(Request $request, $id) {
        //Get order
        $baseOrder = Order::findOrFail($id);

        //query
        $anchor = $baseOrder->date ? Carbon::parse($baseOrder->date) : now();
        $from = $request->filled('from_date')
            ? Carbon::parse($request->from_date)->startOfDay()
            : $anchor->copy()->startOfMonth();
        $to = $request->filled('to_date')
            ? Carbon::parse($request->to_date)->endOfDay()
            : $anchor->copy()->endOfMonth();

        //Get bbookings period
        $ordersQuery = Order::query()
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString());

        if (!empty($baseOrder->assign_supplier)) {
            $ordersQuery->where('assign_supplier', $baseOrder->assign_supplier);
        } else {
            $ordersQuery->where('id', $baseOrder->id);
        }

        if ($request->filled('source')) {
            $ordersQuery->where('source', $request->source);
        }

        $orders = $ordersQuery->orderBy('date')->get();

        //Supplier details
        $supplierModel = !empty($baseOrder->assign_supplier)
            ? AddSupplier::where('add_supplier_id', $baseOrder->assign_supplier)->first()
            : null;

        $currency = $baseOrder->currency ?: 'THB';

        $bookings = [];
        $grossSales = 0;
        $supplierCost = 0;
        $totalPaid = 0;

        $walletDates = SupplierWallet::whereIn('order_id', $orders->pluck('id'))->orderByDesc('timestamp')->get()->groupBy('order_id');
        
        foreach ($orders as $order) {
            $price = BookingPriceHelper::calculate($order);

            $net  = (float) ($price['net_amount'] ?? 0);
            $sell = (float) ($price['sell_amount'] ?? $order->total_amount ?? 0);

            $isPaid = strtolower(trim($order->payment_status ?? '')) === 'paid';
            $paid = $isPaid ? $net : 0;
            $due  = max(0, $net - $paid);

            $adult = (int) ($price['adult'] ?? $order->qty_adult ?? 0);
            $child = (int) ($price['child'] ?? $order->qty_child ?? 0);

            //Payment person name(s) of this booking only
            $personNames = ($walletDates->get($order->id) ?? collect())
                ->pluck('payment_person')
                ->filter()
                ->unique()
                ->implode(', ');

            $bookings[] = [
                'booking_id'  => 'SLW' . str_pad($order->id, 7, '0', STR_PAD_LEFT),
                'travel_date' => $order->start_date ? Carbon::parse($order->start_date)->format('d M Y') : 'N/A',
                'customer'    => trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? '')) ?: 'N/A',
                'service'     => $order->item_name ?? 'N/A',
                'pax'         => $adult . ' Adult · ' . $child . ' Child',
                'net_cost'    => $net,
                'sale_price'  => $sell,
                'paid'        => $paid,
                'payment_person' => $personNames,
                'profit'      => $sell - $net,
                'due'         => $due,
                'paid_date' => $isPaid
            ? (
                $order->paid_payment_date
                    ? Carbon::parse($order->paid_payment_date)->format('d M, Y')
                    : (
                        optional($walletDates->get($order->id)?->first())->travel_date
                            ? Carbon::parse($walletDates->get($order->id)->first()->travel_date)->format('d M, Y')
                            : null
                    )
            )
            : null,
                    ];

            $grossSales   += $sell;
            $supplierCost += $net;
            $totalPaid    += $paid;
        }

        //Payment details + other expenses (supplier wallet)
        $orderIds = $orders->pluck('id');
        $wallets  = SupplierWallet::whereIn('order_id', $orderIds)->orderByDesc('timestamp')->get();
        $lastWallet    = $wallets->first();
        $otherExpenses = (float) $wallets->sum('other_amt');

        $grossProfit = $grossSales - $supplierCost;
        $netProfit   = $grossProfit - $otherExpenses;
        $supplierDue = max(0, $supplierCost - $totalPaid);
        $grossMargin = $grossSales > 0 ? ($grossProfit / $grossSales) * 100 : 0;
        $netMargin   = $grossSales > 0 ? ($netProfit / $grossSales) * 100 : 0;

        if (count($bookings) && $totalPaid <= 0) {
            $paymentStatus = 'Unpaid';
        } elseif ($supplierDue <= 0) {
            $paymentStatus = 'Paid';
        } else {
            $paymentStatus = 'Partially Paid';
        }

        $statement = [
            'invoice_no'        => 'SLW-SUP-' . now()->format('Y') . '-' . str_pad($baseOrder->id, 5, '0', STR_PAD_LEFT),
            'statement_date'    => now()->format('d M Y'),
            'period_from'       => $from->format('d M Y'),
            'period_to'         => $to->format('d M Y'),
            'source'            => $request->filled('source') ? $request->source : ($baseOrder->source ?: 'All Sources'),
            'currency'          => $currency,
            'supplier_name'     => $supplierModel->name ?? $baseOrder->supplier_name ?? 'N/A',
            'supplier_email'    => $supplierModel->email ?? 'N/A',
            'supplier_phone'    => $supplierModel->phone ?? 'N/A',
            'supplier_code'     => $supplierModel->add_supplier_id ?? 'N/A',
            'payment_status'    => $paymentStatus,
            'payment_mode'      => $lastWallet->payment_mode ?? ($baseOrder->payment_mode ?: 'N/A'),
            'payment_reference' => $lastWallet->transaction_id ?? 'N/A',
            'payment_date'      => $lastWallet && $lastWallet->travel_date
                                    ? Carbon::parse($lastWallet->travel_date)->format('d M Y')
                                    : ($baseOrder->paid_payment_date ? Carbon::parse($baseOrder->paid_payment_date)->format('d M Y') : 'N/A'),
            'other_expenses'    => $otherExpenses,
            'bookings'          => $bookings,
        ];

        return view('admin.finances.supplier-payments.supplier-invoice', compact('statement', 'grossSales', 'supplierCost', 'totalPaid', 'grossProfit','netProfit', 'supplierDue', 'grossMargin', 'netMargin'));
    }

    //Function for show invoice of ONE booking only
    public function show_single($id) {
        //Get orders
        $order = Order::findOrFail($id);

        $bookingDate = $order->date ? Carbon::parse($order->date) : now();
        $currency    = $order->currency ?: 'THB';

        $supplierModel = !empty($order->assign_supplier)
            ? AddSupplier::where('add_supplier_id', $order->assign_supplier)->first()
            : null;

        $price = BookingPriceHelper::calculate($order);

        $net  = (float) ($price['net_amount'] ?? 0);
        $sell = (float) ($price['sell_amount'] ?? $order->total_amount ?? 0);

        $isPaid = strtolower(trim($order->payment_status ?? '')) === 'paid';
        $paid   = $isPaid ? $net : 0;
        $due    = max(0, $net - $paid);

        $adult = (int) ($price['adult'] ?? $order->qty_adult ?? 0);
        $child = (int) ($price['child'] ?? $order->qty_child ?? 0);

        // Wallet entries of this booking only
        $wallets    = SupplierWallet::where('order_id', $order->id)->orderByDesc('timestamp')->get();
        $lastWallet = $wallets->first();

        //Payment person name(s) of this booking
        $paymentPersons = $wallets->pluck('payment_person')
            ->filter()
            ->unique()
            ->implode(', ');

        $paidDate = null;
        if ($isPaid) {
            if ($order->paid_payment_date) {
                $paidDate = Carbon::parse($order->paid_payment_date)->format('d M, Y');
            } elseif ($lastWallet && $lastWallet->travel_date) {
                $paidDate = Carbon::parse($lastWallet->travel_date)->format('d M, Y');
            }
        }

        $bookings = [[
            'booking_id'  => 'SLW' . str_pad($order->id, 7, '0', STR_PAD_LEFT),
            'travel_date' => $order->start_date ? Carbon::parse($order->start_date)->format('d M Y') : 'N/A',
            'customer'    => trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? '')) ?: 'N/A',
            'service'     => $order->item_name ?? 'N/A',
            'pax'         => $adult . ' Adult · ' . $child . ' Child',
            'net_cost'    => $net,
            'sale_price'  => $sell,
            'paid'        => $paid,
            'payment_persons' => $paymentPersons,
            'profit'      => $sell - $net,
            'due'         => $due,
            'paid_date'   => $paidDate,
        ]];

        $grossSales   = $sell;
        $supplierCost = $net;
        $totalPaid    = $paid;
        $otherExpenses = (float) $wallets->sum('other_amt');

        $grossProfit = $grossSales - $supplierCost;
        $netProfit   = $grossProfit - $otherExpenses;
        $supplierDue = max(0, $supplierCost - $totalPaid);
        $grossMargin = $grossSales > 0 ? ($grossProfit / $grossSales) * 100 : 0;
        $netMargin   = $grossSales > 0 ? ($netProfit / $grossSales) * 100 : 0;

        if ($totalPaid <= 0) {
            $paymentStatus = 'Unpaid';
        } elseif ($supplierDue <= 0) {
            $paymentStatus = 'Paid';
        } else {
            $paymentStatus = 'Partially Paid';
        }

        $statement = [
            'invoice_no'        => 'SLW-SUP-' . now()->format('Y') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
            'statement_date'    => now()->format('d M Y'),
            'period_from'       => $bookingDate->format('d M Y'),
            'period_to'         => $bookingDate->format('d M Y'),
            'source'            => $order->source ?: 'N/A',
            'currency'          => $currency,
            'supplier_name'     => $supplierModel->name ?? $order->supplier_name ?? 'N/A',
            'supplier_email'    => $supplierModel->email ?? 'N/A',
            'supplier_phone'    => $supplierModel->phone ?? 'N/A',
            'supplier_code'     => $supplierModel->add_supplier_id ?? 'N/A',
            'payment_status'    => $paymentStatus,
            'payment_mode'      => $lastWallet->payment_mode ?? ($order->payment_mode ?: 'N/A'),
            'payment_reference' => $lastWallet->transaction_id ?? 'N/A',
            'payment_date'      => $paidDate
                        ?: ($lastWallet && $lastWallet->timestamp
                            ? Carbon::parse($lastWallet->timestamp)->format('d M, Y')
                            : 'N/A'),
            'other_expenses'    => $otherExpenses,
            'bookings'          => $bookings,
        ];

        return view('admin.finances.supplier-payments.invoice-single', compact(
            'statement', 'grossSales', 'supplierCost', 'totalPaid',
            'grossProfit', 'netProfit', 'supplierDue', 'grossMargin', 'netMargin'
        ));
    }
}