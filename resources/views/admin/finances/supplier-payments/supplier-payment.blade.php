@extends('admin.layouts.master')
@section('content')
@php
    $activePage = 'supplier-payment';
    $fromDate = request(
        'from_date',
        now()->startOfMonth()->format('Y-m-d')
    );
    $toDate = request(
        'to_date',
        now()->endOfMonth()->format('Y-m-d')
    );
@endphp
<div class="supplier-payment-content">
    @include('admin.partials.notification')
    <nav class="page-breadcrumb">
        <a href="{{ url('/admin/dashboard') }}">
            Dashboard
        </a>
        <i class="bi bi-chevron-right"></i>
        <span>Supplier Payments</span>
    </nav>
    <section class="page-heading supplier-payment-heading">
        <div>
            <p>Supplier Management</p>
            <h1>Supplier Payment Update</h1>
            <span>
                Track supplier cost, update payment details and download statements.
            </span>
        </div>
        <button
            type="button"
            class="download-statement"
            id="downloadSupplierStatement"
        >
            <i class="bi bi-download"></i>
            Download Statement
        </button>
    </section>
    {{-- Stats --}}
    <section class="payment-stat-grid">
        <article>
            <span class="stat-icon blue">
                <i class="bi bi-journal-check"></i>
            </span>
            <div>
                <p>Total Bookings</p>
                <h2>
                    {{ $orders->total() }}
                </h2>
                <small>Filtered supplier bookings</small>
            </div>
        </article>
        <article>
            <span class="stat-icon purple">
                <i class="bi bi-receipt"></i>
            </span>
            <div>
                <p>Supplier Net Cost</p>
                <h2>
                    THB {{ number_format($totalNet, 2) }}
                </h2>
                <small>Total payment liability</small>
            </div>
        </article>
        <article>
            <span class="stat-icon green">
                <i class="bi bi-check2-circle"></i>
            </span>
            <div>
                <p>Total Paid</p>
                <h2>
                    THB {{ number_format($totalPaid, 2) }}
                </h2>
                <small>Completed and partial payments</small>
            </div>
        </article>
        <article>
            <span class="stat-icon orange">
                <i class="bi bi-hourglass-split"></i>
            </span>
            <div>
                <p>Outstanding Due</p>
                <h2>
                    THB {{ number_format($totalDue, 2) }}
                </h2>
                <small>Pending supplier payment</small>
            </div>
        </article>
    </section>
    <section class="content-card supplier-payment-card">
        {{-- Search --}}
        <div class="booking-quick-search">
            <div class="quick-search-field">
                <label for="slwNumberSearch">
                    Search by SLW Number
                </label>
                <div>
                    <i class="bi bi-search"></i>
                    <input
                        type="text"
                        id="slwNumberSearch"
                        value="{{ request('slw') }}"
                        placeholder="Example: 86375 or SLW0086375"
                    >
                    <button
                        type="button"
                        id="searchSlwBooking"
                    >
                        Search Booking
                    </button>
                </div>
            </div>
        </div>
        {{-- Filters --}}
        <div class="supplier-filter-bar">
            <div class="filter-field">
                <label for="supplierFromDate">
                    Booking From
                </label>
                <input
                    type="date"
                    class="form-control"
                    id="supplierFromDate"
                    value="{{ request('from_date') }}"
                >
            </div>
            <div class="filter-field">
                <label for="supplierToDate">
                    Booking To
                </label>
                <input
                    type="date"
                    class="form-control"
                    id="supplierToDate"
                    value="{{ request('to_date') }}"
                >
            </div>
            <div class="filter-field">
                <label for="sourceFilter">
                    Source
                </label>
                <select
                    class="form-select"
                    id="sourceFilter"
                >
                    <option value="">
                        All Sources
                    </option>
                    @foreach($sourceOptions as $source)
                        <option
                            value="{{ $source }}"
                            {{ request('source') == $source ? 'selected' : '' }}
                        >
                            {{ $source }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="supplierFilter">
                    Supplier
                </label>
                <select
                    class="form-select"
                    id="supplierFilter"
                >
                    <option value="">
                        All Suppliers
                    </option>
                    @foreach($suppliers as $supplier)
                        <option
                            value="{{ $supplier->add_supplier_id }}"
                            {{ request('supplier') == $supplier->add_supplier_id ? 'selected' : '' }}
                        >
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="paymentStatusFilter">
                    Payment Status
                </label>
                <select
                    class="form-select"
                    id="paymentStatusFilter"
                >
                    <option value="">
                        All Status
                    </option>
                    <option
                        value="Paid"
                        {{ request('payment_status') == 'Paid' ? 'selected' : '' }}
                    >
                        Paid
                    </option>
                    <option
                        value="Partially Paid"
                        {{ request('payment_status') == 'Partially Paid' ? 'selected' : '' }}
                    >
                        Partially Paid
                    </option>
                    <option
                        value="Unpaid"
                        {{ request('payment_status') == 'Unpaid' ? 'selected' : '' }}
                    >
                        Unpaid
                    </option>
                </select>
            </div>
            <button
                type="button"
                class="reset-filter"
                id="resetSupplierFilter"
            >
                <i class="bi bi-arrow-counterclockwise"></i>
                Reset
            </button>
        </div>
        <div class="table-responsive">
            <table
                class="table supplier-payment-table align-middle mb-0"
                id="supplierPaymentTable"
            >
                <thead>
                    <tr>
                        <th>Booking Details</th>
                        <th>Customer</th>
                        <th>Supplier</th>
                        <th>Pax</th>
                        <th>Net Cost</th>
                        <th>Sale Price</th>
                        <th>Profit</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $profit =
                                (float) $booking['sell'] -
                                (float) $booking['net'];
                            $due = max(
                                0,
                                (float) $booking['net'] -
                                (float) $booking['paid']
                            );
                            $type = strtolower(
                                $booking['type'] ?? ''
                            );
                            $statusClass = strtolower(
                                str_replace(
                                    ' ',
                                    '-',
                                    $booking['payment_status'] ?? 'Unpaid'
                                )
                            );
                        @endphp
                        <tr
                            data-id="{{ $booking['id'] }}"
                            data-slw="{{ $booking['slw_no'] }}"
                            data-booking-date="{{ $booking['booking_date'] }}"
                            data-source="{{ $booking['source'] }}"
                            data-supplier="{{ $booking['supplier'] }}"
                            data-status="{{ $booking['payment_status'] }}"
                            data-currency="{{ $booking['currency'] }}"
                            data-net="{{ number_format($booking['net'], 2, '.', '') }}"
                            data-sell="{{ number_format($booking['sell'], 2, '.', '') }}"
                            data-paid="{{ number_format($booking['paid'], 2, '.', '') }}"
                        >
                            {{-- Booking --}}
                            <td>
                                <div class="booking-cell">
                                    <span class="booking-type {{ $type }}">
                                        <i class="bi {{
                                            $type === 'transfer'
                                            ? 'bi-car-front'
                                            : 'bi-map'
                                        }}"></i>
                                    </span>
                                    <div>
                                        <strong>
                                            {{ $booking['product'] }}
                                        </strong>
                                        <small>
                                            <b>{{ $booking['slw_no'] }}</b>
                                            ·
                                            {{ $booking['type'] }}
                                        </small>
                                        <small>
                                            Travel:
                                            @if($booking['travel_date'])
                                                {{ \Carbon\Carbon::parse($booking['travel_date'])->format('d M Y') }}
                                            @else
                                                N/A
                                            @endif
                                            ·
                                            Booked:
                                            @if($booking['booking_date'])
                                                {{ \Carbon\Carbon::parse($booking['booking_date'])->format('d M Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </small>
                                        <small>
                                            Source:
                                            {{ $booking['source'] }}
                                        </small>
                                    </div>
                                </div>

                            </td>
                            {{-- Customer --}}
                            <td>
                                <strong>
                                    {{ $booking['customer'] }}
                                </strong>
                                <small>
                                    <i class="bi bi-telephone"></i>
                                    {{ $booking['phone'] ?: 'N/A' }}
                                </small>
                                <small>
                                    <i class="bi bi-envelope"></i>
                                    {{ $booking['email'] ?: 'N/A' }}
                                </small>
                            </td>
                            {{-- Supplier --}}
                            <td>
                                <strong>
                                    {{ $booking['supplier'] }}
                                </strong>
                                <small>
                                    Assigned supplier
                                </small>
                            </td>
                            {{-- Pax --}}
                            <td>
                                <span class="pax-line">
                                    {{ $booking['adult'] }} Adult
                                </span>
                                <span class="pax-line">
                                    {{ $booking['child'] }} Child
                                </span>
                            </td>
                            {{-- Net --}}
                            <td data-order="{{ $booking['net'] }}">
                                <strong class="net-amount">
                                    {{ $booking['currency'] }}
                                    {{ number_format($booking['net'], 2) }}
                                </strong>
                            </td>
                            {{-- Sale --}}
                            <td data-order="{{ $booking['sell'] }}">
                                <strong class="sale-amount">
                                    {{ $booking['currency'] }}
                                    {{ number_format($booking['sell'], 2) }}
                                </strong>
                            </td>
                            {{-- Profit --}}
                            <td data-order="{{ $profit }}">
                                <strong class="profit-amount">
                                    {{ $booking['currency'] }}
                                    {{ number_format($profit, 2) }}
                                </strong>
                            </td>
                            {{-- Payment --}}
                            <td>
                              <span class="payment-status {{ $statusClass }}">
                                <i></i>
                                {{ $booking['payment_status'] }}
                            </span>
                                <small>
                                    Paid:
                                    {{ $booking['currency'] }}
                                    {{ number_format($booking['paid'], 2) }}
                                </small>
                                <small>
                                    Due:
                                    {{ $booking['currency'] }}
                                    {{ number_format($due, 2) }}
                                </small>
                            </td>
                            {{-- Action --}}
                            <td>
                                <div class="payment-actions">
                               @if(
                                    strtolower(
                                        trim($booking['payment_status'] ?? 'Unpaid')
                                    ) !== 'paid'
                                )
                                        <button
                                            type="button"
                                            class="update-payment-button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#supplierPaymentModal"
                                            data-booking-id="{{ $booking['id'] }}"
                                            data-slw="{{ $booking['slw_no'] }}"
                                            data-supplier="{{ $booking['supplier'] }}"
                                            data-currency="{{ $booking['currency'] }}"
                                            data-net="{{ number_format($booking['net'], 2, '.', '') }}"
                                            data-paid="{{ number_format($booking['paid'], 2, '.', '') }}"
                                        >
                                            <i class="bi bi-cash-coin"></i>
                                            Update Payment
                                        </button>
                                    @endif
                                    <a
                                        href="{{ url('/admin/supplier-invoice/' . $booking['id']) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <i class="bi bi-receipt"></i>
                                        View Invoice
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                No supplier bookings found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">
                            Visible Booking Total
                        </th>
                        <th id="footerNet">
                            THB {{ number_format($totalNet, 2) }}
                        </th>
                        <th id="footerSale">
                            THB {{ number_format($totalSell, 2) }}
                        </th>
                        <th id="footerProfit">
                            THB {{ number_format($totalProfit, 2) }}
                        </th>
                        <th id="footerPaid">
                            THB {{ number_format($totalPaid, 2) }}
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        {{-- Pagination --}}
        <div class="agent-table-footer">
            <p>
                Showing
                <strong>
                    {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }}
                </strong>
                of
                <strong>
                    {{ $orders->total() }}
                </strong>
                bookings
            </p>
            <nav aria-label="Supplier payment pagination">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $orders->onFirstPage() ? 'disabled' : '' }}">
                        <a
                            class="page-link"
                            href="{{ $orders->previousPageUrl() ?? '#' }}"
                        >
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @for($i = 1; $i <= $orders->lastPage(); $i++)

                        @if(
                            $i == 1 ||
                            $i == $orders->lastPage() ||
                            abs($i - $orders->currentPage()) <= 1
                        )
                            <li class="page-item {{ $orders->currentPage() == $i ? 'active' : '' }}">
                                <a
                                    class="page-link"
                                    href="{{ $orders->url($i) }}"
                                >
                                    {{ $i }}
                                </a>
                            </li>
                        @elseif(
                            $i == 2 ||
                            $i == $orders->lastPage() - 1
                        )
                            <li class="page-item">
                                <button
                                    class="page-link"
                                    type="button"
                                >
                                    ...
                                </button>
                            </li>
                        @endif
                    @endfor
                    <li class="page-item {{ !$orders->hasMorePages() ? 'disabled' : '' }}">
                        <a
                            class="page-link"
                            href="{{ $orders->nextPageUrl() ?? '#' }}"
                        >
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </section>
<div
    class="modal fade"
    id="supplierPaymentModal"
    tabindex="-1"
    aria-labelledby="supplierPaymentModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content payment-modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span>
                        <i class="bi bi-cash-coin"></i>
                    </span>
                    <div>
                        <h2
                            class="modal-title"
                            id="supplierPaymentModalLabel"
                        >
                            Update Supplier Payment
                        </h2>
                        <p>
                            Enter payment and settlement details.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>
            </div>
            <form
                id="supplierPaymentForm"
                action="{{ route('admin.supplier.payment.update') }}"
                method="POST"
            >
            @csrf
                <div class="modal-body">
                    <input
                        type="hidden"
                        id="paymentBookingId"
                        name="booking_id"
                    >
                    <section class="modal-booking-summary">
                        <div>
                            <span>SLW Number</span>
                            <strong id="modalSlwNumber">—</strong>
                        </div>
                        <div>
                            <span>Supplier</span>
                            <strong id="modalSupplier">—</strong>
                        </div>
                        <div>
                            <span>Net Payable</span>
                            <strong id="modalNetAmount">
                                THB 0.00
                            </strong>
                        </div>
                        <div>
                            <span>Outstanding Due</span>
                            <strong id="modalDueAmount">
                                THB 0.00
                            </strong>
                        </div>
                    </section>
                    <div class="payment-form-grid">
                        <div class="form-group span-2">
                            <label>
                                Name of Payment Person(s) <b>*</b>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                name="payment_persons"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label>
                                Currency <b>*</b>
                            </label>
                            <select
                                class="form-select"
                                id="paymentCurrency"
                                name="currency"
                                required
                            >
                                <option>THB</option>
                                <option>USD</option>
                                <option>INR</option>
                                <option>SGD</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>
                                Transfer Amount
                            </label>
                            <input
                                type="number"
                                class="form-control split-amount"
                                id="transferAmount"
                                name="transfer_amount"
                                value="0"
                                min="0"
                                step="0.01"
                            >
                        </div>
                        <div class="form-group">
                            <label>
                                Guide Amount
                            </label>
                            <input
                                type="number"
                                class="form-control split-amount"
                                id="guideAmount"
                                name="guide_amount"
                                value="0"
                                min="0"
                                step="0.01"
                            >
                        </div>
                        <div class="form-group">
                            <label>
                                Other Amount
                            </label>
                            <input
                                type="number"
                                class="form-control split-amount"
                                id="otherAmount"
                                name="other_amount"
                                value="0"
                                min="0"
                                step="0.01"
                            >
                        </div>
                            <div class="form-group">
                            <label>
                                Total Paid Amount <b>*</b>
                            </label>
                          <input
                            type="number"
                            class="form-control"
                            id="paidAmount"
                            name="paid_amount"
                            min="0"
                            step="0.01"
                            readonly
                            required
                        >
                        </div>
                        <div class="split-total">
                            <span>
                                Split Amount Total
                            </span>
                            <strong id="splitAmountTotal">
                                THB 0.00
                            </strong>
                        </div>
                        <div class="form-group">
                            <label>
                                Payment Mode <b>*</b>
                            </label>
                            <select
                                class="form-select"
                                name="payment_mode"
                                required
                            >
                                <option value="">
                                    Select payment mode
                                </option>
                                <option>Bank Transfer</option>
                                <option>Cash</option>
                                <option>Credit Card</option>
                                <option>PayPal</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>
                                UTR / Transaction ID <b>*</b>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                name="transaction_reference"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label>
                                Payment Date <b>*</b>
                            </label>
                            <input
                                type="date"
                                class="form-control"
                                name="payment_date"
                                value="{{ now()->format('Y-m-d') }}"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label>
                                Payment Status <b>*</b>
                            </label>
                            <select
                                class="form-select"
                                name="payment_status"
                                required
                            >
                                <option>Paid</option>
                                <option>Partially Paid</option>
                                <option>Pending Verification</option>
                            </select>
                        </div>
                        <div class="form-group span-2">
                            <label>
                                Payment Remark
                            </label>
                            <textarea
                                class="form-control"
                                name="remark"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="cancel-payment"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="save-payment"
                    >
                        <i class="bi bi-check2-circle"></i>
                        Save Payment Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div
    id="supplierPaymentLoader"
    style="
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.35);
        display:none;
        align-items:center;
        justify-content:center;
        z-index:999999;
    "
>
    <div
        style="
            background:#fff;
            padding:22px 28px;
            border-radius:12px;
            text-align:center;
            box-shadow:0 10px 35px rgba(0,0,0,.20);
        "
    >
        <div class="supplier-payment-loader-spinner"></div>
        <strong>
            Loading supplier bookings...
        </strong>
    </div>
</div>
<style>
.supplier-payment-loader-spinner {
    width:42px;
    height:42px;
    margin:0 auto 10px;
    border:4px solid #eeeeee;
    border-top:4px solid #ff654f;
    border-radius:50%;
    animation:supplierPaymentSpin .8s linear infinite;
}
/* Supplier Payment Modal - Fix bottom buttons */
#supplierPaymentModal .modal-dialog {
    max-width: 1400px;
    height: calc(100vh - 30px);
    margin: 15px auto;
}
#supplierPaymentModal .modal-content {
    height: 100%;
    max-height: calc(100vh - 30px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
#supplierPaymentModal form {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
}
#supplierPaymentModal form::-webkit-scrollbar {
    width: 6px;
}
#supplierPaymentModal form::-webkit-scrollbar-thumb {
    background: #c8c8c8;
    border-radius: 10px;
}
@keyframes supplierPaymentSpin {
    to {
        transform:rotate(360deg);
    }
}
</style>
@endsection