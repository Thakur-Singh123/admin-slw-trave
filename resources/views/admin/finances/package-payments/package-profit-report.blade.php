@extends('admin.layouts.master') 
@section('content')
<div class="admin-wrapper">
    @include('admin.partials.notification')
    <div class="package-report-page">
        <nav class="page-breadcrumb"><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="bi bi-chevron-right"></i><span>Package Profit Report</span></nav>
        <section class="page-heading report-page-heading"><div><p>Finance Module</p><h1>Package Booking Profit Report</h1><span>Monitor package revenue, cost, collection, supplier dues and net profit.</span></div><div class="heading-buttons">
        <button type="button" class="export-report" id="exportPackageReport" data-export-url="{{ route('admin.package-payment.export') }}"><i class="bi bi-download"></i> Export CSV</button>
        <a href="{{ route('admin.package-payment.create') }}" class="add-package-button"><i class="bi bi-plus-lg"></i> Add Package Payment</a></div></section>
        <section class="report-stat-grid">
            <article><span class="stat-icon blue"><i class="bi bi-suitcase"></i></span><div><p>Package Bookings</p><h2 id="statBookings">{{ $packages->count() }}</h2><small>Bookings in selected period</small></div></article>
            <article><span class="stat-icon purple"><i class="bi bi-cash-stack"></i></span><div><p>Total Sales</p><h2 id="statSales">THB {{ number_format($totals['sale'], 2) }}</h2><small>Gross package revenue</small></div></article>
            <article><span class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></span><div><p>Net Profit</p><h2 id="statProfit">THB {{ number_format($totals['profit'], 2) }}</h2><small id="statMargin">{{ $totals['sale'] > 0 ? number_format(($totals['profit'] / $totals['sale']) * 100, 2) : '0.00' }}% profit margin</small></div></article>
            <article><span class="stat-icon orange"><i class="bi bi-hourglass-split"></i></span><div><p>Customer Due</p><h2 id="statDue">THB {{ number_format($totals['due'], 2) }}</h2><small>Outstanding collection</small></div></article>
        </section>

        <section class="content-card report-card">
            <div class="report-filter-bar">
                <div class="filter-field"><label for="reportFromDate">Booking From</label><input type="date" class="form-control" id="reportFromDate" value="{{ date('Y-m-01') }}"></div>
                <div class="filter-field"><label for="reportToDate">Booking To</label><input type="date" class="form-control" id="reportToDate" value="{{ date('Y-m-d') }}"></div>
                <div class="filter-field"><label for="reportCountry">Country</label><select class="form-select" id="reportCountry"><option value="">All Countries</option><option>India</option><option>Thailand</option><option>Singapore</option><option>Sri Lanka</option><option>Vietnam</option></select></div>
                <div class="filter-field"><label for="customerPaymentFilter">Customer Payment</label><select class="form-select" id="customerPaymentFilter"><option value="">All Status</option><option>Paid</option><option>Partially Paid</option><option>Unpaid</option></select></div>
                <div class="filter-field"><label for="supplierPaymentFilter">Supplier Payment</label><select class="form-select" id="supplierPaymentFilter"><option value="">All Status</option><option>Paid</option><option>Partially Paid</option><option>Unpaid</option></select></div>
                <div class="filter-field"><label for="profitFilter">Profit Status</label><select class="form-select" id="profitFilter"><option value="">All Profit Status</option><option value="profit">Profit</option><option value="loss">Loss</option></select></div>
                <button type="button" class="reset-report-filter" id="resetPackageFilter"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
            </div>

            <div class="country-summary-strip">
                @foreach(['India','Thailand','Singapore','Sri Lanka','Vietnam'] as $country)
                    <button type="button" data-country="{{ $country }}"><span class="country-code">{{ strtoupper(substr($country, 0, 2)) }}</span><span>{{ $country }}<small>{{ $packages->where('country', $country)->count() }} bookings</small></span></button>
                @endforeach
            </div>

            <div class="table-responsive">
                <table class="table package-report-table align-middle mb-0" id="packageProfitTable">
                    <thead><tr><th>Package &amp; Booking</th><th>Country</th><th>Customer</th><th>Travel / Pax</th><th>Sale Amount</th><th>Total Cost</th><th>Net Profit</th><th>Customer Payment</th><th>Supplier Payment</th><th>Action</th></tr></thead>
                    <tbody>
                    @foreach($packages as $p)
                        @php
                            $baseCost   = (float) $p->total_package_cost;
                            $extraCost  = (float) $p->agent_commission + (float) $p->other_expense;
                            $totalCost  = $baseCost + $extraCost;
                            $sale       = (float) $p->sale_amount;
                            $profit     = (float) $p->net_profit;
                            $margin     = $sale > 0 ? ($profit / $sale) * 100 : 0;
                            $history    = $histories[$p->id] ?? [];
                        @endphp
                        <tr data-id="{{ $p->id }}"
                            data-reference="{{ $p->booking_reference }}"
                            data-package-name="{{ $p->package_name }}"
                            data-currency="{{ $p->currency }}"
                            data-booking-date="{{ optional($p->booking_date)->format('Y-m-d') }}"
                            data-country="{{ $p->country }}"
                            data-customer-status="{{ $p->customer_payment_status }}"
                            data-supplier-status="{{ $p->supplier_payment_status }}"
                            data-sale="{{ number_format($sale, 2, '.', '') }}"
                            data-cost="{{ number_format($totalCost, 2, '.', '') }}"
                            data-profit="{{ number_format($profit, 2, '.', '') }}"
                            data-received="{{ number_format((float) $p->received_amount, 2, '.', '') }}"
                            data-due="{{ number_format((float) $p->customer_due, 2, '.', '') }}"
                            data-supplier-total="{{ number_format($baseCost, 2, '.', '') }}"
                            data-supplier-paid="{{ number_format((float) $p->supplier_paid, 2, '.', '') }}"
                            data-supplier-due="{{ number_format((float) $p->supplier_due, 2, '.', '') }}">
                            <td><div class="package-cell"><span><i class="bi bi-suitcase"></i></span><div><strong>{{ $p->package_name }}</strong><small><b>{{ $p->booking_reference }}</b> · Booked {{ optional($p->booking_date)->format('d M Y') }}</small></div></div></td>
                            <td><span class="country-pill">{{ $p->country }}</span></td>
                            <td><strong>{{ $p->customer_name }}</strong><small>Package customer / agent</small></td>
                            <td><strong>{{ optional($p->travel_from)->format('d M') }} – {{ optional($p->travel_to)->format('d M Y') }}</strong><small>{{ (int) $p->adult_pax }} Adult · {{ (int) $p->child_pax }} Child</small></td>
                            <td data-order="{{ $sale }}"><strong class="sale-value">{{ $p->currency }} {{ number_format($sale, 2) }}</strong></td>
                            <td data-order="{{ $totalCost }}"><strong>{{ $p->currency }} {{ number_format($totalCost, 2) }}</strong><small>Base {{ number_format($baseCost, 2) }} + Extra {{ number_format($extraCost, 2) }}</small></td>
                            <td data-order="{{ $profit }}"><strong class="profit-value {{ $profit < 0 ? 'loss' : '' }}">{{ $p->currency }} {{ number_format($profit, 2) }}</strong><small>{{ number_format($margin, 2) }}% margin</small></td>
                            <td><span class="status-pill {{ \Illuminate\Support\Str::slug($p->customer_payment_status) }}"><i></i>{{ $p->customer_payment_status }}</span><small>Received: {{ number_format((float) $p->received_amount, 2) }}</small><small>Due: {{ number_format((float) $p->customer_due, 2) }}</small></td>
                            <td><span class="status-pill {{ \Illuminate\Support\Str::slug($p->supplier_payment_status) }}"><i></i>{{ $p->supplier_payment_status }}</span><small>Paid: {{ number_format((float) $p->supplier_paid, 2) }}</small><small>Due: {{ number_format((float) $p->supplier_due, 2) }}</small></td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="view-package" data-bs-toggle="modal" data-bs-target="#packageDetailModal" title="View details"><i class="bi bi-eye"></i></button><a href="{{ route('admin.edit.package-payment', ['id' => $p->id]) }}" title="Edit package"><i class="bi bi-pencil"></i></a><button type="button" class="add-package-payment" data-bs-toggle="modal" data-bs-target="#addPaymentModal" title="Add partial payment"><i class="bi bi-cash-coin"></i></button><button type="button" class="view-payment-history" data-history="{{ json_encode($history) }}" data-bs-toggle="modal" data-bs-target="#paymentHistoryModal" title="Payment history"><i class="bi bi-clock-history"></i>@if(count($history) > 0)<span>{{ count($history) }}</span>@endif</button>
                                    <form action="{{ route('admin.package-payment.delete', $p->id) }}"
                                        method="POST"
                                        class="delete-package-form"
                                        style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="delete-package-btn" title="Delete package">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Filtered Total</th>
                            <th id="footerSale">THB {{ number_format($totals['sale'], 2) }}</th>
                            <th id="footerCost">THB {{ number_format($totals['cost'], 2) }}</th>
                            <th id="footerProfit">THB {{ number_format($totals['profit'], 2) }}</th>
                            <th>
                                <div class="footer-line">Received: <span id="footerReceived">THB {{ number_format($totals['received'], 2) }}</span></div>
                                <div class="footer-line">Due: <span id="footerDue">THB {{ number_format($totals['due'], 2) }}</span></div>
                            </th>
                            <th>
                                <div class="footer-line">Paid: <span id="footerSupplierPaid">THB {{ number_format($totals['supplier_paid'], 2) }}</span></div>
                                <div class="footer-line">Due: <span id="footerSupplierDue">THB {{ number_format($totals['supplier_due'], 2) }}</span></div>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

    </div>

</div>


<div class="modal fade" id="packageDetailModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content package-detail-modal"><div class="modal-header"><div><h2>Package Finance Details</h2><p id="detailReference">Booking reference</p></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="detail-title"><span><i class="bi bi-suitcase"></i></span><div><h3 id="detailPackageName">Package Name</h3><p id="detailCountryCustomer">Country · Customer</p></div></div><div class="detail-finance-grid"><div><span>Sale Amount</span><strong id="detailSale">THB 0.00</strong></div><div><span>Total Cost</span><strong id="detailCost">THB 0.00</strong></div><div><span>Net Profit</span><strong id="detailProfit">THB 0.00</strong></div><div><span>Customer Due</span><strong id="detailDue">THB 0.00</strong></div></div><p class="modal-note"><i class="bi bi-info-circle"></i> Detailed component costs can be loaded here from your database when backend integration is completed.</p></div><div class="modal-footer"><button type="button" data-bs-dismiss="modal">Close</button>
{{--<a href="#" id="editPackageLink"><i class="bi bi-pencil"></i> Edit Payment</a>--}}
</div></div></div></div>

<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content finance-action-modal">
            <div class="modal-header"><div class="modal-heading"><span><i class="bi bi-cash-coin"></i></span><div><h2 id="addPaymentModalLabel">Add Package Payment</h2><p id="paymentPackageReference">Select a package booking</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('admin.package-payment.transaction.store') }}" method="post" enctype="multipart/form-data" id="addPackagePaymentForm">
                @csrf
                <input type="hidden" name="package_finance_id" id="paymentPackageId">
                <div class="modal-body">
                    <div class="payment-package-title"><span><i class="bi bi-suitcase"></i></span><div><strong id="paymentPackageName">Package Name</strong><small id="paymentPackageCountry">Country</small></div></div>
                    <div class="payment-balance-grid"><div><span>Total Amount</span><strong id="paymentTotalAmount">THB 0.00</strong></div><div><span>Already Paid</span><strong id="paymentAlreadyPaid">THB 0.00</strong></div><div><span>Remaining Due</span><strong id="paymentRemainingDue">THB 0.00</strong></div></div>
                    <div class="payment-form-grid">
                        <div class="modal-field span-2"><label for="transactionType">Payment Type <b>*</b></label><select class="form-select" name="transaction_type" id="transactionType" required><option value="Customer Receipt">Customer Receipt</option><option value="Supplier Payment">Supplier Payment</option></select><small>Choose whether money was received from the customer or paid to the supplier.</small></div>
                        <div class="modal-field"><label for="transactionAmount">New Payment Amount <b>*</b></label><div class="currency-input"><span id="paymentCurrencyLabel">THB</span><input type="number" class="form-control" name="amount" id="transactionAmount" min="0.01" step="0.01" placeholder="0.00" required></div><small id="amountValidationText">Maximum allowed: THB 0.00</small></div>
                        <div class="modal-field"><label for="transactionDate">Payment Date <b>*</b></label><input type="date" class="form-control" name="payment_date" id="transactionDate" value="<?= date('Y-m-d'); ?>" required></div>
                        <div class="modal-field"><label for="transactionMode">Payment Mode <b>*</b></label><select class="form-select" name="payment_mode" id="transactionMode" required><option value="">Select mode</option><option>Bank Transfer</option><option>Cash</option><option>Credit Card</option><option>UPI</option><option>PayPal</option><option>Other</option></select></div>
                        <div class="modal-field"><label for="transactionReference">UTR / Transaction Reference</label><input type="text" class="form-control" name="transaction_reference" id="transactionReference" maxlength="100" placeholder="Enter payment reference"></div>
                        <div class="modal-field span-2"><label for="transactionRemark">Remark</label><textarea class="form-control" name="remark" id="transactionRemark" rows="3" maxlength="1000" placeholder="Add payment note"></textarea></div>
                        <div class="modal-field span-2"><label for="transactionReceipt">Receipt / Bank Proof</label><div class="modal-upload"><i class="bi bi-cloud-arrow-up"></i><div><strong>Upload payment receipt</strong><small>PDF, JPG or PNG · Maximum 5 MB</small></div><input type="file" name="receipt" id="transactionReceipt" accept=".pdf,.jpg,.jpeg,.png"></div></div>
                    </div>
                    <div class="payment-preview"><span>Balance after this payment</span><strong id="balanceAfterPayment">THB 0.00</strong></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="modal-save" id="savePaymentButton"><i class="bi bi-check2-circle"></i> Save Payment</button>
                    <div class="all-payment-done" id="allPaymentDone" style="display:none;"><i class="bi bi-check-circle-fill"></i> All payment done</div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content finance-action-modal history-modal">
            <div class="modal-header"><div class="modal-heading"><span><i class="bi bi-clock-history"></i></span><div><h2 id="paymentHistoryModalLabel">Payment History</h2><p id="historyPackageReference">Package booking transactions</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="history-package-name" id="historyPackageName">Package Name</div>
                <div class="table-responsive"><table class="table payment-history-table align-middle mb-0"><thead><tr><th>Date</th><th>Transaction Type</th><th>Amount</th><th>Payment Mode</th><th>Reference</th><th>Remark</th><th>Receipt</th><th>Invoice</th></tr></thead><tbody id="paymentHistoryBody"></tbody></table></div>
                <div class="history-empty" id="paymentHistoryEmpty"><i class="bi bi-receipt"></i><strong>No payment transaction found</strong><span>Add the first customer receipt or supplier payment for this package.</span></div>
            </div>
            <div class="modal-footer"><span id="historyTransactionCount">0 transactions</span><button type="button" class="modal-cancel" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3 demo-toast-wrap">
    <div class="toast demo-payment-toast" id="paymentDemoToast" role="status" aria-live="polite" aria-atomic="true">
        <div class="toast-header"><span><i class="bi bi-check2-circle"></i></span><strong class="me-auto">Payment Added</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button></div>
        <div class="toast-body" id="demoPaymentMessage">Payment added to the design preview.</div>
    </div>
</div>
<style>
    /* Popup  */
.modal { z-index: 200000 !important; }
.modal-backdrop { z-index: 199990 !important; }

/* Popup */
#packageDetailModal .modal-dialog,
#addPaymentModal .modal-dialog,
#paymentHistoryModal .modal-dialog {
    max-height: calc(100vh - 2rem);
    margin-top: 1rem;
    margin-bottom: 1rem;
}

.finance-action-modal,
.package-detail-modal {
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 2rem);
    overflow: hidden;
}

/* Add payment */
.finance-action-modal form {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
}

.finance-action-modal .modal-header,
.finance-action-modal .modal-footer,
.package-detail-modal .modal-header,
.package-detail-modal .modal-footer {
    flex-shrink: 0;
}

.finance-action-modal .modal-body,
.package-detail-modal .modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
}

/* Patla scrollbar */
.finance-action-modal .modal-body::-webkit-scrollbar,
.package-detail-modal .modal-body::-webkit-scrollbar { width: 8px; }
.finance-action-modal .modal-body::-webkit-scrollbar-thumb,
.package-detail-modal .modal-body::-webkit-scrollbar-thumb {
    background: #d4d4d8;
    border-radius: 8px;
}
.finance-action-modal .modal-body::-webkit-scrollbar-thumb:hover { background: #ff5d43; }
.all-payment-done {
    align-items: center;
    gap: 8px;
    padding: 11px 20px;
    border-radius: 10px;
    background: #e8f8ee;
    color: #15803d;
    font-weight: 700;
    font-size: 14px;
}
.all-payment-done i { font-size: 18px; }
</style>
@endsection
