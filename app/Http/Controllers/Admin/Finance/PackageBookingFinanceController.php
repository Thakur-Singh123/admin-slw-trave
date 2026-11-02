<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\PackageBookingFinance;
use App\Models\PackagePaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Helpers\ExportHelper;

class PackageBookingFinanceController extends Controller
{
    //Function for package payment report
    public function index() {
        //Get package booking
        $packages = PackageBookingFinance::with('transactions')
            ->orderByDesc('booking_date')
            ->orderByDesc('id')
            ->get();

        //Payment history
        $histories = [];
        foreach ($packages as $p) {
            $histories[$p->id] = $this->paymentHistory($p);
        }

        // $totals = [
        //     'sale'     => (float) $packages->sum('sale_amount'),
        //     'cost'     => (float) $packages->sum(fn ($p) => (float) $p->total_package_cost + (float) $p->agent_commission + (float) $p->other_expense),
        //     'profit'   => (float) $packages->sum('net_profit'),
        //     'received' => (float) $packages->sum('received_amount'),
        //     'due'      => (float) $packages->sum('customer_due'),
        // ];

        $totals = [
            'sale'          => (float) $packages->sum('sale_amount'),
            'cost'          => (float) $packages->sum(fn ($p) => (float) $p->total_package_cost + (float) $p->agent_commission + (float) $p->other_expense),
            'profit'        => (float) $packages->sum('net_profit'),
            'received'      => (float) $packages->sum('received_amount'),
            'due'           => (float) $packages->sum('customer_due'),
            'supplier_paid' => (float) $packages->sum('supplier_paid'),
            'supplier_due'  => (float) $packages->sum('supplier_due'),
        ];

        return view('admin.finances.package-payments.package-profit-report', compact('packages', 'histories', 'totals'));
    }

    //Function for create package payment
    public function create() {
        return view('admin.finances.package-payments.add-package-payment');
    }

    //Function for submit package payment
    public function store(Request $request) {
        //Validate input fileds
        $validated = $request->validate([
            'package_name'            => ['required', 'string', 'max:255'],
            'country'                 => ['required', Rule::in(['India', 'Thailand', 'Singapore', 'Sri Lanka', 'Vietnam'])],
            'booking_date'            => ['required', 'date'],
            'travel_from'             => ['required', 'date'],
            'travel_to'               => ['required', 'date', 'after_or_equal:travel_from'],
            'booking_status'          => ['nullable', Rule::in(['Confirmed', 'Pending', 'Completed', 'Cancelled'])],
            'customer_name'           => ['required', 'string', 'max:255'],
            'customer_email'          => ['nullable', 'email', 'max:255'],
            'customer_phone'          => ['nullable', 'string', 'max:30'],
            'adult_pax'               => ['nullable', 'integer', 'min:1'],
            'child_pax'               => ['nullable', 'integer', 'min:0'],
            'infant_pax'              => ['nullable', 'integer', 'min:0'],

            //'currency'              => ['required', Rule::in(['THB', 'USD', 'INR', 'SGD', 'LKR', 'VND'])],
            'currency' => ['required', 'in:THB'],
            'sale_amount'             => ['required', 'numeric', 'min:0'],
            'received_amount'         => ['nullable', 'numeric', 'min:0'],
            'customer_payment_status' => ['nullable', Rule::in(['Unpaid', 'Partially Paid', 'Paid'])],

            'hotel_cost'              => ['nullable', 'numeric', 'min:0'],
            'transport_cost'          => ['nullable', 'numeric', 'min:0'],
            'tour_cost'               => ['nullable', 'numeric', 'min:0'],
            'guide_cost'              => ['nullable', 'numeric', 'min:0'],
            'visa_cost'               => ['nullable', 'numeric', 'min:0'],
            'other_package_cost'      => ['nullable', 'numeric', 'min:0'],

            'supplier_name'           => ['nullable', 'string', 'max:255'],
            'supplier_paid'           => ['nullable', 'numeric', 'min:0'],
            'supplier_payment_status' => ['nullable', Rule::in(['Unpaid', 'Partially Paid', 'Paid'])],
            'agent_commission'        => ['nullable', 'numeric', 'min:0'],
            'other_expense'           => ['nullable', 'numeric', 'min:0'],

            'payment_mode'            => ['nullable', 'string', 'max:50'],
            'transaction_reference'   => ['nullable', 'string', 'max:255'],
            'payment_date'            => ['nullable', 'date'],
            'remark'                  => ['nullable', 'string'],
            'payment_receipt'         => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        //Server side calculation
        $sale         = (float) ($validated['sale_amount'] ?? 0);
        $received     = (float) ($validated['received_amount'] ?? 0);
        $totalCost    = (float) ($validated['hotel_cost'] ?? 0)
                      + (float) ($validated['transport_cost'] ?? 0)
                      + (float) ($validated['tour_cost'] ?? 0)
                      + (float) ($validated['guide_cost'] ?? 0)
                      + (float) ($validated['visa_cost'] ?? 0)
                      + (float) ($validated['other_package_cost'] ?? 0);
        $supplierPaid = (float) ($validated['supplier_paid'] ?? 0);
        $commission   = (float) ($validated['agent_commission'] ?? 0);
        $otherExpense = (float) ($validated['other_expense'] ?? 0);

        //Checks
        if ($received > $sale) {
            return back()->withInput()->with('unsuccess', 'Amount Received cannot be greater than Package Sale Amount.');
        }
        if ($supplierPaid > $totalCost) {
            return back()->withInput()->with('unsuccess', 'Amount Paid to Supplier cannot be greater than Total Package Cost.');
        }

        $customerDue = max(0, $sale - $received);
        $supplierDue = max(0, $totalCost - $supplierPaid);
        $netProfit   = $sale - $totalCost - $commission - $otherExpense;
        $margin      = $sale > 0 ? ($netProfit / $sale) * 100 : 0;

        $data = $validated;
        unset($data['payment_receipt']);

        $data = array_merge($data, [
            'adult_pax'               => $validated['adult_pax'] ?? 1,
            'child_pax'               => $validated['child_pax'] ?? 0,
            'infant_pax'              => $validated['infant_pax'] ?? 0,
            'booking_status'          => $validated['booking_status'] ?? 'Confirmed',
            'received_amount'         => $received,
            'customer_due'            => $customerDue,
            'customer_payment_status' => $validated['customer_payment_status'] ?? 'Unpaid',
            'hotel_cost'              => $validated['hotel_cost'] ?? 0,
            'transport_cost'          => $validated['transport_cost'] ?? 0,
            'tour_cost'               => $validated['tour_cost'] ?? 0,
            'guide_cost'              => $validated['guide_cost'] ?? 0,
            'visa_cost'               => $validated['visa_cost'] ?? 0,
            'other_package_cost'      => $validated['other_package_cost'] ?? 0,
            'total_package_cost'      => $totalCost,
            'supplier_paid'           => $supplierPaid,
            'supplier_due'            => $supplierDue,
            'supplier_payment_status' => $validated['supplier_payment_status'] ?? 'Unpaid',
            'agent_commission'        => $commission,
            'other_expense'           => $otherExpense,
            'net_profit'              => $netProfit,
            'profit_margin'           => round($margin, 4),
            'created_by'              => auth()->id(),
            'updated_by'              => auth()->id(),
            'booking_reference'       => 'TMP-' . Str::uuid(),
        ]);

        $uploadedPath = null;
        try {
            $booking = DB::transaction(function () use ($data, $request, &$uploadedPath) {

                //Receipt: public/uploads/package-payment
                if ($request->hasFile('payment_receipt')) {
                    $file   = $request->file('payment_receipt');
                    $folder = public_path('uploads/package-payment');

                    if (!File::exists($folder)) {
                        File::makeDirectory($folder, 0755, true);
                    }

                    $fileName = 'receipt_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($folder, $fileName);

                    $uploadedPath = 'uploads/package-payment/' . $fileName;
                    $data['payment_receipt'] = $uploadedPath;
                }

                $booking = PackageBookingFinance::create($data);

                //PKG
                $booking->booking_reference = $this->generateBookingReference($booking);
                $booking->save();

                return $booking;
            });
        } catch (\Throwable $e) {
            if ($uploadedPath && File::exists(public_path($uploadedPath))) {
                File::delete(public_path($uploadedPath));
            }
            report($e);
            return back()->withInput()->with('unsuccess', 'Package payment was not saved. Please try again.');
        }

        return redirect()->route('admin.package-payment.index')->with('success', 'Package payment saved successfully. Booking Reference: ' . $booking->booking_reference);
    }

    //Function for edit package payment
    public function edit($id) {
        //Get package booking finance
        $booking = PackageBookingFinance::findOrFail($id);
        return view('admin.finances.package-payments.edit', compact('booking'));
    }

    // Function for updating package payment
    public function update(Request $request, $id) {
        //Get package booking finance
        $booking = PackageBookingFinance::findOrFail($id);
        //Validate input fields
        $validated = $request->validate([
            'package_name'            => ['required', 'string', 'max:255'],
            'country'                 => ['required', Rule::in(['India', 'Thailand', 'Singapore', 'Sri Lanka', 'Vietnam'])],
            'booking_date'            => ['required', 'date'],
            'travel_from'             => ['required', 'date'],
            'travel_to'               => ['required', 'date', 'after_or_equal:travel_from'],
            'booking_status'          => ['nullable', Rule::in(['Confirmed', 'Pending', 'Completed', 'Cancelled'])],
            'customer_name'           => ['required', 'string', 'max:255'],
            'customer_email'          => ['nullable', 'email', 'max:255'],
            'customer_phone'          => ['nullable', 'string', 'max:30'],
            'adult_pax'               => ['nullable', 'integer', 'min:1'],
            'child_pax'               => ['nullable', 'integer', 'min:0'],
            'infant_pax'              => ['nullable', 'integer', 'min:0'],

            'currency'                => ['required', 'in:THB'],
            'sale_amount'             => ['required', 'numeric', 'min:0'],
            'received_amount'         => ['nullable', 'numeric', 'min:0'],
            'customer_payment_status' => ['nullable', Rule::in(['Unpaid', 'Partially Paid', 'Paid'])],

            'hotel_cost'              => ['nullable', 'numeric', 'min:0'],
            'transport_cost'          => ['nullable', 'numeric', 'min:0'],
            'tour_cost'               => ['nullable', 'numeric', 'min:0'],
            'guide_cost'              => ['nullable', 'numeric', 'min:0'],
            'visa_cost'               => ['nullable', 'numeric', 'min:0'],
            'other_package_cost'      => ['nullable', 'numeric', 'min:0'],

            'supplier_name'           => ['nullable', 'string', 'max:255'],
            'supplier_paid'           => ['nullable', 'numeric', 'min:0'],
            'supplier_payment_status' => ['nullable', Rule::in(['Unpaid', 'Partially Paid', 'Paid'])],
            'agent_commission'        => ['nullable', 'numeric', 'min:0'],
            'other_expense'           => ['nullable', 'numeric', 'min:0'],

            'payment_mode'            => ['nullable', 'string', 'max:50'],
            'transaction_reference'   => ['nullable', 'string', 'max:255'],
            'payment_date'            => ['nullable', 'date'],
            'remark'                  => ['nullable', 'string'],
            'payment_receipt'         => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        //Server side calculation
        $sale         = (float) ($validated['sale_amount'] ?? 0);
        $received     = (float) ($validated['received_amount'] ?? 0);
        $totalCost    = (float) ($validated['hotel_cost'] ?? 0)
                      + (float) ($validated['transport_cost'] ?? 0)
                      + (float) ($validated['tour_cost'] ?? 0)
                      + (float) ($validated['guide_cost'] ?? 0)
                      + (float) ($validated['visa_cost'] ?? 0)
                      + (float) ($validated['other_package_cost'] ?? 0);
        $supplierPaid = (float) ($validated['supplier_paid'] ?? 0);
        $commission   = (float) ($validated['agent_commission'] ?? 0);
        $otherExpense = (float) ($validated['other_expense'] ?? 0);

        //Checks
        if ($received > $sale) {
            return back()->withInput()->with('unsuccess', 'Amount Received cannot be greater than Package Sale Amount.');
        }
        if ($supplierPaid > $totalCost) {
            return back()->withInput()->with('unsuccess', 'Amount Paid to Supplier cannot be greater than Total Package Cost.');
        }

        $customerDue = max(0, $sale - $received);
        $supplierDue = max(0, $totalCost - $supplierPaid);
        $netProfit   = $sale - $totalCost - $commission - $otherExpense;
        $margin      = $sale > 0 ? ($netProfit / $sale) * 100 : 0;

        $data = $validated;
        unset($data['payment_receipt']);

        $data = array_merge($data, [
            'adult_pax'               => $validated['adult_pax'] ?? 1,
            'child_pax'               => $validated['child_pax'] ?? 0,
            'infant_pax'              => $validated['infant_pax'] ?? 0,
            'booking_status'          => $validated['booking_status'] ?? 'Confirmed',
            'received_amount'         => $received,
            'customer_due'            => $customerDue,
            'customer_payment_status' => $validated['customer_payment_status'] ?? 'Unpaid',
            'hotel_cost'              => $validated['hotel_cost'] ?? 0,
            'transport_cost'          => $validated['transport_cost'] ?? 0,
            'tour_cost'               => $validated['tour_cost'] ?? 0,
            'guide_cost'              => $validated['guide_cost'] ?? 0,
            'visa_cost'               => $validated['visa_cost'] ?? 0,
            'other_package_cost'      => $validated['other_package_cost'] ?? 0,
            'total_package_cost'      => $totalCost,
            'supplier_paid'           => $supplierPaid,
            'supplier_due'            => $supplierDue,
            'supplier_payment_status' => $validated['supplier_payment_status'] ?? 'Unpaid',
            'agent_commission'        => $commission,
            'other_expense'           => $otherExpense,
            'net_profit'              => $netProfit,
            'profit_margin'           => round($margin, 4),
            'updated_by'              => auth()->id(),
        ]);

        $uploadedPath = null;
        try {
            DB::transaction(function () use ($data, $request, $booking, &$uploadedPath) {
                //Handle new receipt upload if present
                if ($request->hasFile('payment_receipt')) {
                    //Delete old file if exists
                    if ($booking->payment_receipt && File::exists(public_path($booking->payment_receipt))) {
                        File::delete(public_path($booking->payment_receipt));
                    }

                    $file   = $request->file('payment_receipt');
                    $folder = public_path('uploads/package-payment');

                    if (!File::exists($folder)) {
                        File::makeDirectory($folder, 0755, true);
                    }

                    $fileName = 'receipt_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($folder, $fileName);

                    $uploadedPath = 'uploads/package-payment/' . $fileName;
                    $data['payment_receipt'] = $uploadedPath;
                }

                $booking->update($data);
            });
        } catch (\Throwable $e) {
            if ($uploadedPath && File::exists(public_path($uploadedPath))) {
                File::delete(public_path($uploadedPath));
            }
            report($e);
            return back()->withInput()->with('unsuccess', 'Package payment was not updated. Please try again.');
        }

        return redirect()->route('admin.package-payment.index')->with('success', 'Package payment updated successfully. Booking Reference: ' . $booking->booking_reference);
    }

    //Function for add partial payment
    public function storePayment(Request $request) {
        //Validate input fields
        $validated = $request->validate([
            'package_finance_id'    => ['required', 'integer', 'exists:package_booking_finance,id'],
            'transaction_type'      => ['required', Rule::in(['Customer Receipt', 'Supplier Payment'])],
            'amount'                => ['required', 'numeric', 'min:0.01'],
            'payment_date'          => ['required', 'date'],
            'payment_mode'          => ['required', 'string', 'max:50'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'remark'                => ['nullable', 'string', 'max:1000'],
            'receipt'               => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $uploadedPath = null;

        try {
            $result = DB::transaction(function () use ($validated, $request, &$uploadedPath) {

                //Lock booking to prevent simultaneous payment issues
                $booking = PackageBookingFinance::lockForUpdate()
                    ->findOrFail($validated['package_finance_id']);

                $amount = round((float) $validated['amount'], 2);

                $isSupplier = $validated['transaction_type'] === 'Supplier Payment';

                $total = round(
                    (float) ($isSupplier ? $booking->total_package_cost : $booking->sale_amount),
                    2
                );

                $paid = round(
                    (float) ($isSupplier ? $booking->supplier_paid : $booking->received_amount),
                    2
                );

                $due = round(max(0, $total - $paid), 2);

                // Check remaining due
                if ($due <= 0) {
                    throw new \DomainException(
                        'All payment is already done for this booking.'
                    );
                }

                if ($amount > $due) {
                    throw new \DomainException(
                        'Amount cannot be greater than remaining due (' .
                        $booking->currency . ' ' . number_format($due, 2) . ').'
                    );
                }

                //Receipt upload
                $receiptPath = null;

                if ($request->hasFile('receipt')) {
                    $file = $request->file('receipt');

                    $folder = public_path('uploads/package-payment');

                    if (!File::exists($folder)) {
                        File::makeDirectory($folder, 0755, true);
                    }

                    $fileName = 'receipt_' . time() . '_' .
                        Str::random(8) . '.' .
                        $file->getClientOriginalExtension();

                    $file->move($folder, $fileName);

                    $uploadedPath = 'uploads/package-payment/' . $fileName;
                    $receiptPath = $uploadedPath;
                }

                //Save payment transaction
                PackagePaymentTransaction::create([
                    'package_finance_id'    => $booking->id,
                    'transaction_type'      => $validated['transaction_type'],
                    'amount'                => $amount,
                    'currency'              => $booking->currency,
                    'payment_mode'          => $validated['payment_mode'],
                    'transaction_reference' => $validated['transaction_reference'] ?? null,
                    'payment_date'          => $validated['payment_date'],
                    'receipt_path'          => $receiptPath,
                    'remark'                => $validated['remark'] ?? null,
                    'created_by'            => auth()->id(),
                    'created_at'            => now(),
                ]);

                //Update booking totals
                $newPaid = round($paid + $amount, 2);
                $newDue = round(max(0, $total - $newPaid), 2);

                $status = $newDue <= 0 ? 'Paid' : 'Partially Paid';

                if ($isSupplier) {
                    $booking->supplier_paid = $newPaid;
                    $booking->supplier_due = $newDue;
                    $booking->supplier_payment_status = $status;
                } else {
                    $booking->received_amount = $newPaid;
                    $booking->customer_due = $newDue;
                    $booking->customer_payment_status = $status;
                }

                $booking->updated_by = auth()->id();
                $booking->save();

                return [
                    'id' => $booking->id,
                    'due' => $newDue,
                ];
            });

        } catch (\DomainException $e) {

            //Delete uploaded receipt if payment fails
            if ($uploadedPath && File::exists(public_path($uploadedPath))) {
                File::delete(public_path($uploadedPath));
            }

            return redirect()->back()->withInput()->with('unsuccess', $e->getMessage());

        } catch (\Throwable $e) {

            //Delete uploaded receipt if payment fails
            if ($uploadedPath && File::exists(public_path($uploadedPath))) {
                File::delete(public_path($uploadedPath));
            }

            report($e);

            return redirect()->back()->withInput()->with('unsuccess', 'Payment was not saved. Please try again.');
        }

        //Get updated booking
        $booking = PackageBookingFinance::find($result['id']);

        //Success message
        $message = $result['due'] <= 0
            ? 'Payment saved successfully. All payment is done.'
            : 'Payment saved successfully.';

        return redirect()
            ->route('admin.package-payment.index')
            ->with('success', $message . ' Booking Reference: ' . $booking->booking_reference);
    }

    //Function for delete pakage booking finance
    public function destroy($id) {
        //Get pakage booking finance
        $booking = PackageBookingFinance::with('transactions')->findOrFail($id);

        try {
            DB::transaction(function () use ($booking) {
                //Delete transaction receipt files
                foreach ($booking->transactions as $transaction) {
                    if (
                        $transaction->receipt_path &&
                        File::exists(public_path($transaction->receipt_path))
                    ) {
                        File::delete(public_path($transaction->receipt_path));
                    }
                }

                //Delete booking receipt
                if (
                    $booking->payment_receipt &&
                    File::exists(public_path($booking->payment_receipt))
                ) {
                    File::delete(public_path($booking->payment_receipt));
                }

                //Delete payment transactions
                $booking->transactions()->delete();

                //Delete booking
                $booking->delete();
            });

            return redirect()->route('admin.package-payment.index')->with('success', 'Package payment deleted successfully.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.package-payment.index')->with('unsuccess', 'Unable to delete package payment.');
        }
    }

    //Function for export booking finance
    public function export(Request $request) {
        //Get package booking finance
        $query = PackageBookingFinance::query();

        if ($request->filled('from_date')) {
            $query->whereDate('booking_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('booking_date', '<=', $request->to_date);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('customer_status')) {
            $query->where('customer_payment_status', $request->customer_status);
        }

        if ($request->filled('supplier_status')) {
            $query->where('supplier_payment_status', $request->supplier_status);
        }

        if ($request->profit_status === 'profit') {
            $query->where('net_profit', '>=', 0);
        } elseif ($request->profit_status === 'loss') {
            $query->where('net_profit', '<', 0);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                    ->orWhere('package_name', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $packages = $query->orderByDesc('booking_date')->orderByDesc('id')->get();

        $headings = ['Reference','Package','Country','Customer','Booking Date','Sale','Total Cost','Net Profit','Received','Due','Customer Status','Supplier Status'];

        $rows = $packages->map(function ($p) {
            return [
                $p->booking_reference,
                $p->package_name,
                $p->country,
                $p->customer_name,
                $p->booking_date,
                $p->sale_amount,
                ($p->total_package_cost ?? 0)
                    + ($p->agent_commission ?? 0)
                    + ($p->other_expense ?? 0),
                $p->net_profit,
                $p->received_amount,
                $p->customer_due,
                $p->customer_payment_status,
                $p->supplier_payment_status,
            ];
        })->toArray();

        return ExportHelper::download('package-profit-report.xlsx', 'Package Profit Report', $headings, $rows );
    }

    //Function for payment history
    private function paymentHistory(PackageBookingFinance $p): array {
        $history = [];
        $date    = optional($p->payment_date)->format('Y-m-d') ?: optional($p->booking_date)->format('Y-m-d');

        $openingCustomer = round((float) $p->received_amount - (float) $p->transactions->where('transaction_type', 'Customer Receipt')->sum('amount'), 2);
        $openingSupplier = round((float) $p->supplier_paid - (float) $p->transactions->where('transaction_type', 'Supplier Payment')->sum('amount'), 2);

        foreach ([['Customer Receipt', $openingCustomer], ['Supplier Payment', $openingSupplier]] as [$type, $amount]) {
            if ($amount > 0) {
                $history[] = [
                    'transaction_type'      => $type,
                    'amount'                => $amount,
                    'currency'              => $p->currency,
                    'payment_mode'          => $p->payment_mode,
                    'transaction_reference' => $p->transaction_reference,
                    'payment_date'          => $date,
                    'receipt_path'          => $p->payment_receipt ? asset($p->payment_receipt) : '',
                    'remark'                => $p->remark ?: 'Initial payment',
                    'invoice_url'           => route('admin.package-payment.initial-invoice', [
                        'id'   => $p->id,
                        'type' => $type === 'Customer Receipt' ? 'customer' : 'supplier',
                    ]),
                ];
            }
        }

        foreach ($p->transactions as $t) {
            $history[] = [
                'transaction_type'      => $t->transaction_type,
                'amount'                => (float) $t->amount,
                'currency'              => $t->currency ?: $p->currency,
                'payment_mode'          => $t->payment_mode,
                'transaction_reference' => $t->transaction_reference,
                'payment_date'          => optional($t->payment_date)->format('Y-m-d'),
                'receipt_path'          => $t->receipt_path ? asset($t->receipt_path) : '',
                'remark'                => $t->remark,
                'invoice_url'           => route('admin.package-payment.invoice', $t->id),
            ];
        }

        usort($history, fn ($a, $b) => strcmp((string) $b['payment_date'], (string) $a['payment_date']));

        return $history;
    }

    //Function for booking reference 
    private function generateBookingReference(PackageBookingFinance $booking): string {
        //Get date
        $date      = Carbon::parse($booking->booking_date)->format('Ymd');
        $reference = 'PKG' . $date . str_pad((string) $booking->id, 4, '0', STR_PAD_LEFT);
        if (PackageBookingFinance::where('booking_reference', $reference)
                ->where('id', '!=', $booking->id)->exists()) {
            throw new \RuntimeException('Duplicate booking reference: ' . $reference);
        }
        return $reference;
    }

    //Invoice for a payment transaction
    public function invoice($id) {
        //Get detail
        $t       = PackagePaymentTransaction::findOrFail($id);
        $booking = PackageBookingFinance::with('transactions')->findOrFail($t->package_finance_id);

        $isSupplier = $t->transaction_type === 'Supplier Payment';
        $sameType   = $booking->transactions->where('transaction_type', $t->transaction_type);

        $totalPaidNow = (float) ($isSupplier ? $booking->supplier_paid : $booking->received_amount);
        $opening      = round($totalPaidNow - (float) $sameType->sum('amount'), 2);
        $previous     = round($opening + (float) $sameType->where('id', '<', $t->id)->sum('amount'), 2);

        $invoice = $this->buildInvoice(
            $booking,
            $isSupplier,
            (float) $t->amount,
            $previous,
            optional($t->payment_date)->format('d M Y'),
            $t->payment_mode,
            $t->transaction_reference,
            $t->remark,
            'INV' . Carbon::parse($t->payment_date)->format('Ymd') . str_pad((string) $t->id, 4, '0', STR_PAD_LEFT)
        );

        return view('admin.finances.package-payments.invoice', compact('invoice'));
    }

    //Invoice for initial payment
    public function initialInvoice($id, $type) {
        //Get booking
        $booking    = PackageBookingFinance::with('transactions')->findOrFail($id);
        $isSupplier = $type === 'supplier';
        $txType     = $isSupplier ? 'Supplier Payment' : 'Customer Receipt';

        $totalPaid = (float) ($isSupplier ? $booking->supplier_paid : $booking->received_amount);
        $opening   = round($totalPaid - (float) $booking->transactions->where('transaction_type', $txType)->sum('amount'), 2);

        abort_if($opening <= 0, 404);

        $date = optional($booking->payment_date)->format('d M Y') ?: optional($booking->booking_date)->format('d M Y');

        $invoice = $this->buildInvoice(
            $booking,
            $isSupplier,
            $opening,
            0,
            $date,
            $booking->payment_mode,
            $booking->transaction_reference,
            $booking->remark ?: 'Initial payment',
            'INV' . $booking->id . ($isSupplier ? 'S' : 'C') . '0000'
        );

        return view('admin.finances.package-payments.invoice', compact('invoice'));
    }

    //Build invoice data
    private function buildInvoice(PackageBookingFinance $b, bool $isSupplier, float $current, float $previous, ?string $date, ?string $mode, ?string $ref, ?string $remark, string $invoiceNo): array {
        //Get details
        $total     = (float) ($isSupplier ? $b->total_package_cost : $b->sale_amount);
        $totalPaid = round($previous + $current, 2);
        $remaining = round(max(0, $total - $totalPaid), 2);
        
        //Invoice
        return [
            'invoice_no'            => $invoiceNo,
            'payment_date'          => $date ?: '-',
            'package_reference'     => $b->booking_reference,
            'package_name'          => $b->package_name,
            'country'               => $b->country,
            'travel_date'           => optional($b->travel_from)->format('d M Y') . ' – ' . optional($b->travel_to)->format('d M Y'),
            'is_supplier'           => $isSupplier,
            'party_name'            => $isSupplier ? ($b->supplier_name ?: 'Supplier') : $b->customer_name,
            'party_email'           => $isSupplier ? '' : $b->customer_email,
            'transaction_type'      => $isSupplier ? 'Supplier Payment' : 'Customer Receipt',
            'payment_mode'          => $mode ?: '-',
            'transaction_reference' => $ref ?: '-',
            'currency'              => $b->currency,
            'package_total'         => $total,
            'previous_paid'         => $previous,
            'current_payment'       => $current,
            'total_paid'            => $totalPaid,
            'remaining_balance'     => $remaining,
            'status'                => $remaining <= 0 ? 'Paid' : 'Partially Paid',
            'remark'                => $remark ?: '-',
        ];
    }
}