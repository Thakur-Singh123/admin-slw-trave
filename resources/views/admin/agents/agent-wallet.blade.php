@extends('admin.layouts.master')
@section('content')
@php
  use App\Helpers\WalletHelper;
@endphp
@php
    $initials = collect(explode(' ', $agent->name ?? 'Agent'))
        ->filter()
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->implode('');

    $status = (int) $agent->isactive === 1 ? 'Active' : 'Inactive';
    $statusClass = strtolower($status);

    $runningBalance = $balance;
@endphp
<div class="wallet-page-content">
    <nav class="page-breadcrumb">
      @include('admin.partials.notification')
      <a href="{{ route('admin.agents') }}">
          Agents
      </a>
      <i class="bi bi-chevron-right"></i>
      <span>
          {{ $agent->name ?? 'Agent' }}
      </span>
    </nav>
    <section class="page-heading wallet-page-heading">
        <div class="heading-agent">
            <span class="heading-avatar">
                {{ $initials }}
            </span>
            <div>
                <p>
                    AGT-{{ $agent->add_agent_id }}
                </p>
                <h1>
                    {{ $agent->name ?? 'N/A' }}
                </h1>
                <span>
                    {{ $agent->email ?? 'N/A' }}
                </span>
            </div>
        </div>
        <div class="heading-actions">
            <a
                href="{{ route('admin.agents') }}"
                class="outline-button"
            >
                <i class="bi bi-arrow-left"></i>
                Agent List
            </a>
            @if(strtolower(trim(auth()->user()->adm_privi ?? '')) === 'admin')
            <button
                type="button"
                class="primary-button"
                data-bs-toggle="modal"
                data-bs-target="#walletTopupModal"
            >
                <i class="bi bi-plus-lg"></i>
                Add Top-up
            </button>
            @endif
        </div>
    </section>
    <section class="wallet-summary-grid">
        <article class="wallet-summary-card total-credit">
            <span class="summary-icon">
                <i class="bi bi-arrow-down-left"></i>
            </span>
            <div>
                <p>
                    Total Amount Credited
                </p>
                <h2>
                    ${{ number_format(WalletHelper::deposit($agent->add_agent_id), 2) }}
                </h2>
                <small>
                    All successful wallet top-ups
                </small>
            </div>
        </article>
        <article class="wallet-summary-card total-used">
            <span class="summary-icon">
                <i class="bi bi-arrow-up-right"></i>
            </span>
            <div>
                <p>
                    Total Used Amount
                </p>
                <h2>
                    ${{ number_format(WalletHelper::withdrawal($agent->add_agent_id), 2) }}
                </h2>
                <small>
                    Used against confirmed bookings
                </small>
            </div>
        </article>
        <article class="wallet-summary-card available-balance">
            <span class="summary-icon">
                <i class="bi bi-wallet2"></i>
            </span>
            <div>
                <p>
                    Available Balance
                </p>
                <h2>
                    ${{ number_format(WalletHelper::balance($agent->add_agent_id), 2) }}
                </h2>
                <small>
                    Correct current wallet balance
                </small>
            </div>
        </article>
    </section>
    <!-- Agent Information -->
    <section class="agent-information-grid">
        <article class="content-card agent-details-card">
            <div class="card-heading">
                <div>
                    <h3>
                        Agent Details
                    </h3>
                    <p>
                        Account and contact information.
                    </p>
                </div>
                <a href="#">
                    <i class="bi bi-pencil"></i>
                    Edit Details
                </a>
            </div>
            <div class="agent-detail-list">
                <div class="detail-item">
                    <span class="detail-icon">
                        <i class="bi bi-person"></i>
                    </span>
                    <div>
                        <small>
                            Contact Person
                        </small>

                        <strong>
                            {{ $agent->contact_person ?? 'N/A' }}
                        </strong>
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-icon">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <div>
                        <small>
                            Email Address
                        </small>
                        <strong>
                            {{ $agent->email ?? 'N/A' }}
                        </strong>
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-icon">
                        <i class="bi bi-telephone"></i>
                    </span>
                    <div>
                        <small>
                            Mobile Number
                        </small>
                        <strong>
                            {{ $agent->telephone ?? 'N/A' }}
                        </strong>
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-icon">
                        <i class="bi bi-hash"></i>
                    </span>
                    <div>
                        <small>
                            Agent ID
                        </small>
                        <strong>
                            AGT-{{ $agent->add_agent_id }}
                        </strong>
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-icon">
                        <i class="bi bi-briefcase"></i>
                    </span>
                    <div>
                        <small>
                            Business Type
                        </small>
                        <strong>
                            {{ $agent->business_type ?? 'N/A' }}
                        </strong>
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-icon">
                        <i class="bi bi-geo-alt"></i>
                    </span>
                    <div>
                        <small>
                            Country / City
                        </small>
                        <strong>
                            {{ $agent->country ?? 'N/A' }}
                            /
                            {{ $agent->city ?? 'N/A' }}
                        </strong>
                    </div>
                </div>
                <div class="detail-item full-width">
                    <span class="detail-icon">
                        <i class="bi bi-building"></i>
                    </span>
                    <div>
                        <small>
                            Office Address
                        </small>
                        <strong>
                            {{ $agent->address ?? 'N/A' }}
                        </strong>
                    </div>
                </div>
            </div>
        </article>
        <article class="content-card account-details-card">
            <div class="card-heading">
                <div>
                    <h3>
                        Account Details
                    </h3>
                    <p>
                        Status and security information.
                    </p>
                </div>
            </div>
            <div class="account-profile-block">
                <span class="large-agent-avatar">
                    {{ $initials }}
                </span>
                <div>
                    <h4>
                        {{ $agent->name ?? 'N/A' }}
                    </h4>
                    <p>
                        {{ $agent->email ?? 'N/A' }}
                    </p>
                    <span class="account-status {{ $statusClass }}">
                        <i></i>
                        {{ $status }}
                    </span>
                </div>
            </div>
            <div class="account-meta-list">
                <div>
                    <span>
                        Referral Code
                    </span>
                    <strong>
                        {{ $agent->referral_code ?? 'N/A' }}
                    </strong>
                </div>
                <div>
                    <span>
                        Registered On
                    </span>
                    <strong>
                        @if($agent->date)
                            {{ \Carbon\Carbon::parse($agent->date)->format('d M Y') }}

                        @else
                            N/A
                        @endif
                    </strong>
                </div>
                <div>
                    <span>
                        Last Login
                    </span>
                    <strong>
                        {{ $agent->last_login ?? 'N/A' }}
                    </strong>
                </div>
                <div>
                    <span>
                        Password
                    </span>
                    <strong style="color: #198754;">
                        {{ $agent->password ?? 'N/A' }}
                    </strong>
                </div>
            </div>
        </article>
    </section>
    <section class="content-card wallet-transactions-card">
        <div class="card-heading">
            <div>
                <h3>
                    Wallet Transactions
                </h3>
                <p>
                    Complete wallet credit and usage history.
                </p>
            </div>
            <!-- <button
                type="button"
                class="export-transactions-button"
            >
                <i class="bi bi-download"></i>
                Export
            </button> -->

        </div>
        <!-- Transaction Toolbar -->
        <div class="transaction-toolbar">
            <div class="transaction-search">
                <i class="bi bi-search"></i>
                <input
                    type="search"
                    id="transactionSearch"
                    data-agent-id="{{ $agent->add_agent_id }}"
                    placeholder="Search transaction, booking or reference..."
                >
            </div>
            <div class="transaction-filters">
                <input
                    type="date"
                    class="form-control"
                    id="transactionFromDate"
                    title="From date"
                >


                <input
                    type="date"
                    class="form-control"
                    id="transactionToDate"
                    title="To date"
                >


                <select
                    class="form-select"
                    id="transactionTypeFilter"
                >

                    <option value="all">
                        All Transactions
                    </option>

                    <option value="credit">
                        Top-up / Credit
                    </option>

                    <option value="debit">
                        Booking / Debit
                    </option>

                </select>
                <a
                    href="{{ route('admin.agent.wallet', $agent->add_agent_id) }}"
                    class="clear-filter-button"
                >
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Clear
                </a>
            </div>
        </div>
        <!-- Transaction Table -->
        <div class="table-responsive">
        <table class="table wallet-transaction-table align-middle mb-0" id="transactionTable">
        <thead>
            <tr>
                <th>
                    Wallet ID
                </th>
                <th>
                    Transaction ID
                </th>
                <th>
                    Type
                </th>
                <th>
                    Details
                </th>
                <th>
                    Date
                </th>
                <th>
                    Credit
                </th>
                <th>
                    Debit
                </th>
                <th>
                    Balance
                </th>
                <th>
                    Reference
                </th>
                <th>
                    Invoice
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                @php
                    $credit = (float) (
                        $transaction->display_credit ?? 0
                    );
                    $debit = (float) (
                        $transaction->display_debit ?? 0
                    );
                    $type = $transaction->display_type
                        ?? (
                            $credit > 0
                                ? 'credit'
                                : 'debit'
                        );

                    $transactionDate =
                        $transaction->display_date
                        ??
                        (
                            $type === 'credit'
                                ? $transaction->deposit_date
                                : $transaction->withdrawal_date
                        );

                    $reference =
                        $transaction->order_id
                        ??
                        $transaction->trasaction_number
                        ??
                        'N/A';
                    $transactionId =
                        $transaction->trasaction_number
                        ??
                        'TXN-' . $transaction->id;
                    $detail =
                        $transaction->account_name
                        ??
                        $transaction->payment_mathod
                        ??
                        'Wallet Transaction';
                    $comment =
                        $transaction->commets
                        ?? '';
                    $balanceAtTransaction =
                        $transaction->display_balance
                        ?? $runningBalance;
                    $alreadyRefunded =
                        (float) (
                            $transaction->cancel_amount ?? 0
                        );
                    $remainingRefund =
                        max(
                            $debit - $alreadyRefunded,
                            0
                        );
                @endphp
                <tr
                    data-type="{{ $type }}"
                    data-date="{{ $transactionDate
                        ? \Carbon\Carbon::parse($transactionDate)->format('Y-m-d')
                        : '' }}"
                >
                    <td>
                        <strong>
                            WAL-{{ $transaction->id }}
                        </strong>
                    </td>
                    <td>
                        <strong>
                            {{ $transactionId }}
                        </strong>
                        <small>
                            @if($type === 'refund')
                                Refund
                            @elseif($type === 'credit')
                                Wallet Top-up
                            @else
                                Booking Payment
                            @endif
                        </small>
                    </td>
                    <td>
                        <span class="type-badge {{ $type }}">
                            <i class="bi
                                {{ $type === 'credit'
                                    ? 'bi-arrow-down-left'
                                    : (
                                        $type === 'refund'
                                            ? 'bi-arrow-counterclockwise'
                                            : 'bi-arrow-up-right'
                                    )
                                }}">
                            </i>
                            {{ $type === 'credit'
                                ? 'Credit'
                                : (
                                    $type === 'refund'
                                        ? 'Refund'
                                        : 'Debit'
                                )
                            }}
                        </span>
                    </td>
                    <td>
                        <strong>
                            {{ $detail }}
                        </strong>
                        @if(!empty($comment))
                            <small>
                                {{ $comment }}
                            </small>
                        @endif
                        @if(
                            $type === 'debit'
                            &&
                            !empty($transaction->order_id)
                        )
                            <small>
                                SLW:- {{ $transaction->order_id }}
                            </small>

                        @endif
                    </td>
                    <td>
                        @if($transactionDate)
                            {{ \Carbon\Carbon::parse($transactionDate)->format('d M Y') }}
                            <small>
                                {{ \Carbon\Carbon::parse($transactionDate)->format('h:i A') }}
                            </small>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($credit > 0)
                            <strong class="credit-value">
                                +${{ number_format($credit, 2) }}
                            </strong>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($debit > 0)
                            @if($type === 'debit')
                                <button
                                    type="button"
                                    class="debit-value"
                                    data-bs-toggle="modal"
                                    data-bs-target="#walletRefundModal{{ $transaction->id }}"
                                    style="
                                        border:0;
                                        background:transparent;
                                        padding:0;
                                        cursor:pointer;
                                    "
                                >
                                    -${{ number_format($debit, 2) }}
                                </button>
                            @else
                                <strong class="debit-value">
                                    -${{ number_format($debit, 2) }}
                                </strong>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <strong>
                            ${{ number_format(
                                $balanceAtTransaction,
                                2
                            ) }}
                        </strong>

                    </td>
                    <td>
                        {{ $reference }}

                    </td>
                    <td>
                        @if($reference !== 'N/A')
                            <a
                                href="{{ route(
                                    'admin.agent.wallet.invoice',
                                    $transaction->id
                                ) }}"
                                class="invoice-button"
                            >
                                <i class="bi bi-receipt"></i>
                                Invoice
                            </a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="10"
                        class="text-center py-4"
                    >
                        No wallet transactions found.

                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@foreach($transactions as $transaction)
    @php
        $credit = (float) (
            $transaction->display_credit ?? 0
        );
        $debit = (float) (
            $transaction->display_debit ?? 0
        );
        $type = $transaction->display_type
            ?? (
                $credit > 0
                    ? 'credit'
                    : 'debit'
            );
        $alreadyRefunded = (float) (
            $transaction->cancel_amount ?? 0
        );

        $remainingRefund = max(
            $debit - $alreadyRefunded,
            0
        );
    @endphp
    @if(
        $type === 'debit'
        &&
        $debit > 0
        &&
        $remainingRefund > 0
    )
        <div
            class="modal fade"
            id="walletRefundModal{{ $transaction->id }}"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content wallet-topup-modal">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">
                                Refund Wallet Amount
                            </h5>
                            <small>
                                {{ $agent->name ?? 'N/A' }}
                                ·
                                AGT-{{ $agent->add_agent_id }}
                                @if(!empty($transaction->order_id))
                                    ·
                                    Order ID: {{ $transaction->order_id }}
                                @endif
                            </small>
                        </div>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>
                    <form
                        action="{{ route(
                            'admin.agent.wallet.refund'
                        ) }}"
                        method="POST"
                    >
                        @csrf
                        <input
                            type="hidden"
                            name="wallet_id"
                            value="{{ $transaction->id }}"
                        >
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">
                                        Refund Amount
                                    </label>
                                    <div class="modal-amount-input">
                                        <span>
                                            $
                                        </span>
                                      <input
                                        type="number"
                                        name="refund_amount"
                                        value="{{ $remainingRefund }}"
                                        style="cursor:not-allowed; background:#f8f9fa;"
                                    >
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">
                                        Remarks
                                    </label>
                                    <textarea
                                        name="remarks"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Refund remarks..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-light"
                                data-bs-dismiss="modal"
                            >
                                Cancel
                            </button>
                        <button
                            type="submit"
                            class="primary-button"
                            onclick="
                                event.preventDefault();

                                Swal.fire({
                                    title: 'Confirm Refund?',
                                    html: `
                                        <div style='font-size:14px; color:#6c757d; margin-top:5px;'>
                                            Are you sure you want to refund this payment?
                                        </div>

                                        <div style='
                                            margin-top:18px;
                                            padding:14px 20px;
                                            background:#fff5f5;
                                            border:1px solid #ffd6d6;
                                            border-radius:10px;
                                        '>
                                            <div style='
                                                font-size:12px;
                                                color:#888;
                                                margin-bottom:4px;
                                            '>
                                                Refund Amount
                                            </div>

                                            <strong style='
                                                font-size:24px;
                                                color:#16a34a;
                                            '>
                                                ${{ number_format($remainingRefund, 2) }}
                                            </strong>
                                        </div>
                                    `,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    reverseButtons: false,
                                    buttonsStyling: false,

                                    confirmButtonText: '<i class=&quot;bi bi-check-lg&quot;></i> Yes, Refund',
                                    cancelButtonText: 'Cancel',

                                    customClass: {
                                        popup: 'refund-swal-popup',
                                        title: 'refund-swal-title',
                                        actions: 'refund-swal-actions',
                                        confirmButton: 'refund-confirm-btn',
                                        cancelButton: 'refund-cancel-btn'
                                    }

                                }).then((result) => {

                                    if (result.isConfirmed) {
                                        this.form.requestSubmit();
                                    }

                                });

                                return false;
                            "
                        >
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Confirm Refund
                        </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    @endforeach
        <!-- Transaction Footer -->
        <div class="transaction-footer">
            <p>
                Showing
                <strong>
                    {{ $transactions->firstItem() ?? 0 }}
                    –
                    {{ $transactions->lastItem() ?? 0 }}
                </strong>
                of
                <strong>
                    {{ $transactions->total() }}
                </strong>
                transactions
            </p>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    {{-- Previous --}}
                    <li
                        class="page-item
                        {{ $transactions->onFirstPage() ? 'disabled' : '' }}"
                    >
                        @if($transactions->currentPage() == 2)
                            <a
                                class="page-link"
                                href="{{ route('admin.agent.wallet', $agent->add_agent_id) }}"
                            >
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        @else
                            <a
                                class="page-link"
                                href="{{ request()->fullUrlWithQuery([
                                    'page' => $transactions->currentPage() - 1
                                ]) }}"
                            >
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        @endif
                    </li>
                    {{-- Pages --}}
                    @php
                        $current = $transactions->currentPage();
                        $last = $transactions->lastPage();
                        $start = max(
                            1,
                            min($current - 1, $last - 2)
                        );
                        $end = min(
                            $last,
                            $start + 2
                        );
                    @endphp
                    @for($page = $start; $page <= $end; $page++)
                        @php
                            $pageUrl = $page == 1
                                ? route(
                                    'admin.agent.wallet',
                                    $agent->add_agent_id
                                )
                                : request()->fullUrlWithQuery([
                                    'page' => $page
                                ]);
                        @endphp
                        <li
                            class="page-item
                            {{ $page == $current ? 'active' : '' }}"
                        >
                            <a
                                class="page-link"
                                href="{{ $pageUrl }}"
                            >
                                {{ $page }}
                            </a>
                        </li>
                    @endfor
                    {{-- Next --}}
                    <li
                        class="page-item
                        {{ $transactions->hasMorePages()
                            ? ''
                            : 'disabled' }}"
                    >
                        @if($transactions->hasMorePages())
                            <a
                                class="page-link"
                                href="{{ request()->fullUrlWithQuery([
                                    'page' => $transactions->currentPage() + 1
                                ]) }}"
                            >
                                <i class="bi bi-chevron-right"></i>

                            </a>
                        @else
                            <a
                                class="page-link"
                                href="#"
                            >
                                <i class="bi bi-chevron-right"></i>

                            </a>
                        @endif
                    </li>
                </ul>
            </nav>
        </div>
    </section>
</div>
<!-- Wallet Top-up Modal -->
<div
    class="modal fade"
    id="walletTopupModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content wallet-topup-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        Add Wallet Top-up
                    </h5>
                    <small>
                        {{ $agent->name ?? 'N/A' }}
                        ·
                        AGT-{{ $agent->add_agent_id }}
                    </small>
                </div>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>
            </div>
            <form id="specificAgentTopupForm">
                @csrf
                <input
                    type="hidden"
                    name="add_agent_id"
                    id="topupAgentId"
                    value="{{ $agent->add_agent_id }}"
                >
                <div class="modal-body">
                    <div class="current-balance-strip">
                        <span>
                            Current Balance
                        </span>
                        <strong>
                            ${{ number_format(
                                WalletHelper::balance($agent->add_agent_id),
                                2
                            ) }}
                        </strong>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">
                                Top-up Amount
                            </label>
                            <div class="modal-amount-input">
                                <span>
                                    $
                                </span>
                                <input
                                    type="number"
                                    name="amount"
                                    id="specificTopupAmount"
                                    min="1"
                                    step="0.01"
                                    placeholder="0.00"
                                >
                                <select
                                    name="currency"
                                    id="specificTopupCurrency"
                                >
                                    <option value="USD">
                                        USD
                                    </option>
                                    <option value="THB">
                                        THB
                                    </option>
                                    <option value="INR">
                                        INR
                                    </option>
                                    <option value="SGD">
                                        SGD
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">
                                Payment Method
                            </label>
                            <select
                                class="form-select"
                                name="payment_method"
                                id="specificPaymentMethod"
                            >
                                <option value="">
                                    Select
                                </option>
                                <option value="Flywire Link">
                                    Flywire Link
                                </option>
                                <option value="Bangkok Account">
                                    Bangkok Account
                                </option>
                                <option value="ICICI India Account">
                                    ICICI India Account (AMD)
                                </option>
                                <option value="Credit Wallet">
                                    Credit Wallet
                                </option>
                                <option value="Other">
                                    Other
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Payment Reference
                            </label>
                            <input
                                type="text"
                                name="payment_reference"
                                class="form-control"
                                id="specificPaymentReference"
                                placeholder="UTR / Transaction ID"
                            >
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Remarks
                            </label>
                            <textarea
                                name="remarks"
                                class="form-control"
                                id="specificRemarks"
                                rows="3"
                                placeholder="Internal remarks..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-balance-preview">
                        <span>
                            Balance after this top-up
                        </span>
                        <strong id="specificNewBalance">
                            ${{ number_format(
                                WalletHelper::balance($agent->add_agent_id),
                                2
                            ) }}
                        </strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="primary-button disable-submit"
                    >
                        <i class="bi bi-check-lg"></i>
                        Confirm Top-up
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection