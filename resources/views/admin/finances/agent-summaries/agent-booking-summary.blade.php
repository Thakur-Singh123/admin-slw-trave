@extends('admin.layouts.master')
@section('content')
<style>
    /* =========================================================
   FIX FILTER + TABLE HOVER MOVEMENT
   ========================================================= */

.agent-summary-card,
.agent-summary-card .agent-filter-bar,
.agent-summary-card .agent-filter-bar .filter-field,
.agent-summary-card .agent-filter-bar .form-control,
.agent-summary-card .agent-filter-bar .form-select,
.agent-summary-card .agent-filter-bar button,
.agent-summary-card .agent-table-note,
.agent-summary-card .agent-search-row,
.agent-summary-card .table-responsive,
.agent-summary-card .agent-summary-table,
.agent-summary-card .agent-summary-table thead,
.agent-summary-card .agent-summary-table tbody,
.agent-summary-card .agent-summary-table tfoot,
.agent-summary-card .agent-summary-table tr,
.agent-summary-card .agent-summary-table td,
.agent-summary-card .agent-summary-table th {
    box-sizing: border-box !important;
    transform: none !important;
}


/* Filter bar fixed */
.agent-summary-card .agent-filter-bar,
.agent-summary-card .agent-filter-bar .filter-field {
    position: static !important;
    top: auto !important;
    left: auto !important;
    margin-top: 0 !important;
}


/* Filter controls fixed on hover */
.agent-summary-card .agent-filter-bar .form-control:hover,
.agent-summary-card .agent-filter-bar .form-select:hover,
.agent-summary-card .agent-filter-bar button:hover {
    position: static !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
}


/* Prevent button movement */
.agent-summary-card .apply-filter:hover,
.agent-summary-card .reset-filter:hover {
    transform: none !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}


/* Search fixed */
.agent-summary-card .agent-search-box,
.agent-summary-card .agent-search-box .form-control {
    transform: none !important;
}


/* Table fixed */
.agent-summary-card .table-responsive {
    position: static !important;
    transform: none !important;
}


.agent-summary-card .agent-summary-table {
    position: static !important;
    transform: none !important;
    transition: none !important;
}


/* Table rows fixed */
.agent-summary-card .agent-summary-table tbody tr,
.agent-summary-card .agent-summary-table tbody tr:hover {
    position: static !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
    margin: 0 !important;
}


/* Table cells fixed */
.agent-summary-card .agent-summary-table tbody tr td,
.agent-summary-card .agent-summary-table tbody tr:hover td {
    position: static !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
}


/* Only background change on row hover */
.agent-summary-card .agent-summary-table tbody tr:hover {
    background: #fffaf8 !important;
    box-shadow: none !important;
}

/* =========================================================
   AGENT TABLE SORT
   ========================================================= */

.agent-sort {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0;
    margin: 0;
    color: #8994a4;
    background: transparent;
    border: 0;
    outline: 0;
    box-shadow: none;
    font-size: 8px;
    font-weight: 800;
    text-transform: uppercase;
    white-space: nowrap;
    cursor: pointer;
}

.agent-sort:hover {
    color: var(--slw-primary);
}

.agent-sort i {
    font-size: 8px;
    transition: none !important;
}

.agent-sort.active {
    color: var(--slw-primary);
}

.agent-sort.active.asc i {
    transform: rotate(180deg) !important;
}

.agent-sort.active.desc i {
    transform: rotate(0deg) !important;
}
.agent-status::before,
.agent-status::after {
    display: none !important;
    content: none !important;
}

.agent-status i {
    display: inline-block !important;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    margin-right: 5px;
}
/* Sort header - no red color */
.agent-sort,
.agent-sort.active,
.agent-sort:hover {
    color: #8994a4 !important;
}

.agent-sort i {
    color: #8994a4 !important;
}

.agent-sort.active i,
.agent-sort.active.asc i,
.agent-sort.active.desc i {
    color: #8994a4 !important;
}

/* ===== Layout fix only ===== */
.agent-summary-content {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
}

.agent-summary-card {
    width: 100% !important;
    max-width: 100% !important;
    display: block !important;
    overflow: hidden !important;
}

.agent-summary-card .agent-filter-bar {
    width: 100% !important;
    display: grid !important;
    grid-template-columns: repeat(4, minmax(140px, 1fr)) auto !important;
    align-items: end !important;
    float: none !important;
    clear: both !important;
}

.agent-summary-card .agent-table-note,
.agent-summary-card .agent-search-row,
.agent-summary-card .table-responsive {
    width: 100% !important;
    max-width: 100% !important;
    float: none !important;
    clear: both !important;
}

.agent-summary-card .agent-table-note {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
}

.agent-summary-card .agent-search-row {
    display: block !important;
}

.agent-summary-card .agent-search-box {
    width: 100% !important;
    max-width: 520px !important;
}

.agent-summary-card .table-responsive {
    display: block !important;
    overflow-x: auto !important;
}

.agent-summary-card .agent-summary-table {
    display: table !important;
    width: 100% !important;
    min-width: 1050px !important;
    max-width: none !important;
    table-layout: auto !important;
}

.agent-summary-card .agent-summary-table thead {
    display: table-header-group !important;
}

.agent-summary-card .agent-summary-table tbody {
    display: table-row-group !important;
}

.agent-summary-card .agent-summary-table tfoot {
    display: table-footer-group !important;
}

.agent-summary-card .agent-summary-table tr {
    display: table-row !important;
}

.agent-summary-card .agent-summary-table th,
.agent-summary-card .agent-summary-table td {
    display: table-cell !important;
    vertical-align: middle !important;
}
/* =========================================================
   AJAX LOADER
   ========================================================= */

#agentBookingAjaxLoader {
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgb(0 0 0 / 35%);
}

.agent-booking-loader-box {
    padding: 20px 28px;
    text-align: center;
    background: #fff;
    border-radius: 10px;
}

.agent-booking-loader-spinner {
    width: 40px;
    height: 40px;
    margin: 0 auto 10px;
    border: 4px solid #eee;
    border-top: 4px solid #ff5d43;
    border-radius: 50%;
    animation: agentBookingSpin .8s linear infinite;
}

@keyframes agentBookingSpin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 1200px) {
    .agent-summary-card .agent-filter-bar {
        grid-template-columns: repeat(2, minmax(140px, 1fr)) auto !important;
    }
}

@media (max-width: 700px) {
    .agent-summary-card .agent-filter-bar {
        grid-template-columns: 1fr !important;
    }

    .agent-summary-card .agent-table-note {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 8px !important;
    }
}

/* ===== DataTables ===== */
.agent-summary-card .dataTables_wrapper {
    width: 100%;
    padding: 0 18px;
    box-sizing: border-box;
}

.agent-summary-card .dataTables_wrapper .dataTables_length,
.agent-summary-card .dataTables_wrapper .dataTables_filter {
    padding: 15px 0;
    margin: 0;
    font-size: 9px;
    color: #8994a4;
}

.agent-summary-card .dataTables_wrapper .dataTables_length {
    float: left;
}

.agent-summary-card .dataTables_wrapper .dataTables_filter {
    float: right;
}

.agent-summary-card .dataTables_wrapper .dataTables_length select {
    height: 36px;
    min-width: 70px;
    margin: 0 6px;
    padding: 0 25px 0 10px;
    border: 1px solid #e4eaf1;
    border-radius: 7px;
    background: #fff;
    font-size: 9px;
    color: #344054;
    outline: none;
}

.agent-summary-card .dataTables_wrapper .dataTables_filter input {
    width: 260px;
    height: 38px;
    margin-left: 8px;
    padding: 0 12px;
    border: 1px solid #e4eaf1;
    border-radius: 8px;
    background: #fff;
    font-size: 9px;
    color: #344054;
    outline: none;
}

.agent-summary-card .dataTables_wrapper .dataTables_info {
    float: left;
    padding: 15px 0;
    font-size: 9px;
    color: #8994a4;
}

.agent-summary-card .dataTables_wrapper .dataTables_paginate {
    float: right;
    padding: 10px 0 15px;
}

.agent-summary-card .dataTables_wrapper .dataTables_paginate .paginate_button {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    margin: 0 2px !important;
    padding: 0 9px !important;
    border: 1px solid #e4eaf1 !important;
    border-radius: 6px !important;
    background: #fff !important;
    color: #344054 !important;
    font-size: 9px;
    box-shadow: none !important;
}

.agent-summary-card .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    border-color: #ffb2a4 !important;
    background: #fffaf8 !important;
    color: var(--slw-primary) !important;
}

.agent-summary-card .dataTables_wrapper .dataTables_paginate .paginate_button.current,
.agent-summary-card .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    border-color: var(--slw-primary) !important;
    background: var(--slw-primary) !important;
    color: #fff !important;
}

.agent-summary-card .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
.agent-summary-card .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
    background: #f8f9fa !important;
    color: #a8b0bb !important;
    cursor: not-allowed;
}

.agent-summary-card .dataTables_wrapper .dataTables_scroll {
    clear: both;
}

.agent-summary-card .dataTables_wrapper .dataTables_scrollBody {
    overflow-x: auto !important;
    border: 0 !important;
}

.agent-summary-card .dataTables_wrapper::after {
    content: "";
    display: table;
    clear: both;
}

/* DataTable duplicate footer fix */
.agent-summary-card .dataTables_scrollBody #agentBookingTable tfoot {
    display: none !important;
}

@media (max-width: 700px) {
    .agent-summary-card .dataTables_wrapper .dataTables_length,
    .agent-summary-card .dataTables_wrapper .dataTables_filter,
    .agent-summary-card .dataTables_wrapper .dataTables_info,
    .agent-summary-card .dataTables_wrapper .dataTables_paginate {
        float: none;
        text-align: left;
    }

    .agent-summary-card .dataTables_wrapper .dataTables_filter input {
        width: 100%;
        margin: 8px 0 0;
    }
}
</style>
<div class="agent-summary-content">
    {{-- Breadcrumb --}}
    <nav class="page-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">
            Dashboard
        </a>
        <i class="bi bi-chevron-right"></i>
        <span>
            Agent Booking Summary
        </span>
    </nav>
    {{-- Heading --}}
    <section class="page-heading agent-summary-heading">
        <div>
            <p>
                Agent Performance
            </p>
            <h1>
                Agent Booking Summary
            </h1>
            <span>
                Only agents who have created bookings through the portal are listed here.
            </span>
        </div>
        <button
            type="button"
            class="export-button"
            id="exportAgentSummary"
        >
            <i class="bi bi-download"></i>
            Export CSV
        </button>
    </section>
    {{-- Stats --}}
    <section class="agent-stat-grid">
        {{-- Booking Agents --}}
        <article>
            <span class="stat-icon blue">
                <i class="bi bi-people"></i>
            </span>
            <div>
                <p>
                    Booking Agents
                </p>
                <h2 id="cardAgents">
                    {{ $totalAgentCount }}
                </h2>
                <small>
                    Agents with at least 1 booking
                </small>
            </div>
        </article>
        {{-- Total Bookings --}}
        <article>
            <span class="stat-icon purple">
                <i class="bi bi-journal-check"></i>
            </span>
            <div>
                <p>
                    Total Bookings
                </p>
                <h2 id="cardBookings">
                    {{ number_format($totalBookings) }}

                </h2>
                <small>
                    Selected booking period
                </small>
            </div>
        </article>
        {{-- Gross Sales --}}
        <article>
            <span class="stat-icon green">
                <i class="bi bi-cash-stack"></i>
            </span>
            <div>
                <p>
                    Gross Sales
                </p>
                <h2 id="cardSales">
                    THB {{ number_format($totalSell, 2) }}
                </h2>
                <small>
                    Total selling amount
                </small>
            </div>
        </article>
        {{-- Wallet --}}
        <article>
            <span class="stat-icon orange">
                <i class="bi bi-wallet2"></i>
            </span>
            <div>
                <p>
                    Total Wallet Balance
                </p>
                <h2 id="cardWallet">
                    USD {{ number_format($totalWallet, 2) }}
                </h2>
                <small>
                    Available agent wallets
                </small>
            </div>
        </article>
    </section>
    {{-- Main Card --}}
    <section class="content-card agent-summary-card">
        {{-- Filter Bar --}}
        <div class="agent-filter-bar">
            {{-- From Date --}}
            <div class="filter-field">
                <label for="agentFromDate">
                    Booking From Date
                </label>
                <input
                    type="date"
                    class="form-control"
                    id="agentFromDate"
                    value="{{ $fromDate }}"
                >
            </div>
            {{-- To Date --}}
            <div class="filter-field">
                <label for="agentToDate">
                    Booking To Date
                </label>
                <input
                    type="date"
                    class="form-control"
                    id="agentToDate"
                    value="{{ $toDate }}"
                >
            </div>
            {{-- Country --}}
            <div class="filter-field">
                <label for="agentCountryFilter">
                    Country
                </label>
                <select
                    class="form-select"
                    id="agentCountryFilter"
                >
                    <option value="">
                        All Countries
                    </option>
                    @foreach($countries as $country)
                        <option
                            value="{{ $country }}"
                            {{ request('country') == $country ? 'selected' : '' }}
                        >
                            {{ $country }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- Status --}}
            <div class="filter-field">
                <label for="agentStatusFilter">
                    Status
                </label>
                <select
                    class="form-select"
                    id="agentStatusFilter"
                >
                    <option value="">
                        All Status
                    </option>
                    <option
                        value="Active"
                        {{ request('status') == 'Active' ? 'selected' : '' }}
                    >
                        Active
                    </option>
                    <option
                        value="Inactive"
                        {{ request('status') == 'Inactive' ? 'selected' : '' }}
                    >
                        Inactive
                    </option>
                </select>
            </div>
            {{-- Reset --}}
            <button
                type="button"
                class="reset-filter"
                id="resetAgentFilter"
            >
                <i class="bi bi-arrow-counterclockwise"></i>
                Reset
            </button>
        </div>
        {{-- Table Note --}}
        <div class="agent-table-note">
            <span>
                <i class="bi bi-info-circle"></i>
                Click an agent's booking count to open the settlement statement in a new tab.
            </span>
            <span id="activePeriod">
                {{ date('d M Y', strtotime($fromDate)) }}
                –
                {{ date('d M Y', strtotime($toDate)) }}
            </span>
        </div>
        {{-- Table --}}
        <div class="table-responsive">
            <table
                class="table agent-summary-table align-middle mb-0"
                id="agentBookingTable"
            >
                {{-- Table Header --}}
                <thead>
                    <tr>
                        {{-- Agent Information --}}
                        <th>
                            <button
                                type="button"
                                class="agent-sort"
                                data-sort="company"
                            >
                                Agent Information
                                <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        {{-- Country --}}
                        <th>
                            <button
                                type="button"
                                class="agent-sort"
                                data-sort="country"
                            >
                                Country
                                <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        {{-- Total Bookings --}}
                        <th>
                            <button
                                type="button"
                                class="agent-sort"
                                data-sort="bookings"
                            >
                                Total Bookings
                                <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        {{-- Net Cost --}}
                        <th>
                            <button
                                type="button"
                                class="agent-sort"
                                data-sort="net"
                            >
                                Net Cost
                                <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        {{-- Gross Sales --}}
                        <th>
                            <button
                                type="button"
                                class="agent-sort"
                                data-sort="sell"
                            >
                                Gross Sales
                                <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        {{-- Margin --}}
                        <th>
                            <button
                                type="button"
                                class="agent-sort"
                                data-sort="margin"
                            >
                                Margin
                                <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        {{-- Wallet --}}
                        <th>
                            <button
                                type="button"
                                class="agent-sort"
                                data-sort="wallet"
                            >
                                Wallet Balance
                                <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        {{-- Last Booking --}}
                        <th>
                            <button
                                type="button"
                                class="agent-sort"
                                data-sort="last_booking"
                            >
                                Last Booking
                                <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        {{-- Status --}}
                        <th>
                            <button
                                type="button"
                                class="agent-sort"
                                data-sort="status"
                            >
                                Status
                                <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                    </tr>
                </thead>
                {{-- Table Body --}}
                <tbody>
                    @forelse($agents as $agent)
                        @php
                            $margin =
                                (float) $agent['sell']
                                -
                                (float) $agent['net'];
                            $marginPercentage =
                                $agent['sell'] > 0
                                    ? (
                                        $margin /
                                        $agent['sell']
                                    ) * 100
                                    : 0;
                        @endphp
                        <tr
                            data-agent-id="{{ (int) $agent['id'] }}"
                            data-last-booking="{{ $agent['last_booking'] }}"
                            data-bookings="{{ (int) $agent['bookings'] }}"
                            data-net="{{ number_format(
                                $agent['net'],
                                2,
                                '.',
                                ''
                            ) }}"
                            data-sell="{{ number_format(
                                $agent['sell'],
                                2,
                                '.',
                                ''
                            ) }}"
                            data-wallet="{{ number_format(
                                $agent['wallet'],
                                2,
                                '.',
                                ''
                            ) }}"
                        >
                            {{-- Agent Information --}}
                            <td>
                                <div class="agent-cell">
                                    <span>
                                        {{
                                            strtoupper(
                                                substr(
                                                    $agent['company'],
                                                    0,
                                                    2
                                                )
                                            )
                                        }}
                                    </span>
                                    <div>
                                        <strong>
                                            {{ $agent['company'] }}
                                        </strong>
                                        <small>
                                            {{ $agent['code'] }}
                                            ·
                                            {{ $agent['name'] }}
                                        </small>
                                        <a
                                            href="mailto:{{ $agent['email'] }}"
                                        >
                                            {{ $agent['email'] }}

                                        </a>
                                        <small>

                                            {{ $agent['phone'] }}

                                        </small>
                                    </div>
                                </div>
                            </td>
                            {{-- Country --}}
                            <td>
                                <span class="country-name">
                                    <i class="bi bi-geo-alt"></i>
                                    {{ $agent['country'] }}

                                </span>
                            </td>
                            {{-- Total Bookings --}}
                            <td
                                data-order="{{ (int) $agent['bookings'] }}"
                            >
                            <a
                                class="booking-count-link"
                                href="{{ route('admin.agent.booking.settlement', [
                                    'agent_id' => $agent['id'],
                                    'from' => $fromDate,
                                    'to' => $toDate
                                ]) }}"
                                target="_blank"
                                rel="noopener"
                            >
                                <strong>
                                    {{ number_format($agent['bookings']) }}
                                </strong>

                                <small>
                                    View settlement
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </small>
                            </a>
                            </td>
                            {{-- Net Cost --}}
                            <td
                                data-order="{{ $agent['net'] }}"
                            >
                                <strong>
                                    THB

                                    {{ number_format(
                                        $agent['net'],
                                        2
                                    ) }}
                                </strong>
                            </td>
                            {{-- Gross Sales --}}
                            <td
                                data-order="{{ $agent['sell'] }}"
                            >
                                <strong class="sales-amount">
                                    THB

                                    {{ number_format(
                                        $agent['sell'],
                                        2
                                    ) }}
                                </strong>
                            </td>
                            {{-- Margin --}}
                            <td
                                data-order="{{ $margin }}"
                            >
                                <strong
                                    class="margin-amount"
                                    style="{{ $margin < 0
                                        ? 'color:#dc3545 !important;'
                                        : '' }}"
                                >
                                    THB

                                    {{ number_format(
                                        $margin,
                                        2
                                    ) }}
                                </strong>
                                <small>

                                    {{ number_format(
                                        $marginPercentage,
                                        1
                                    ) }}%
                                </small>
                            </td>
                            {{-- Wallet --}}
                            <td
                                data-order="{{ $agent['wallet'] }}"
                            >
                                <strong class="wallet-amount">
                                    USD

                                    {{ number_format(
                                        $agent['wallet'],
                                        2
                                    ) }}
                                </strong>
                            </td>
                            {{-- Last Booking --}}
                            <td
                                data-order="{{
                                    $agent['last_booking']
                                        ? strtotime($agent['last_booking'])
                                        : 0
                                }}"
                            >
                                @if($agent['last_booking'])
                                    <strong>

                                        {{ date(
                                            'd M Y',
                                            strtotime(
                                                $agent['last_booking']
                                            )
                                        ) }}
                                    </strong>
                                    <small>

                                        {{ date(
                                            'l',
                                            strtotime(
                                                $agent['last_booking']
                                            )
                                        ) }}

                                    </small>
                                @else
                                    <strong>
                                        N/A
                                    </strong>
                                @endif
                            </td>
                            {{-- Status --}}
                            <td>
                                <span
                                    class="agent-status {{ strtolower(
                                        $agent['status']
                                    ) }}"
                                >
                                    <i></i>

                                    {{ $agent['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="9"
                                class="text-center"
                            >
                                No agent bookings found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                {{-- Footer --}}
                <tfoot>
                    <tr>
                        <th colspan="2">
                            Filtered Total
                        </th>
                        <th id="footerBookings">

                            {{ number_format(
                                $totalBookings
                            ) }}
                        </th>
                        <th id="footerNet">
                            THB
                            {{ number_format(
                                $totalNet,
                                2
                            ) }}
                        </th>
                        <th id="footerSales">
                            THB

                            {{ number_format(
                                $totalSell,
                                2
                            ) }}
                        </th>
                        <th id="footerMargin">
                            THB
                            {{ number_format(
                                $totalMargin,
                                2
                            ) }}
                        </th>
                        <th id="footerWallet">
                            USD
                            {{ number_format(
                                $totalWallet,
                                2
                            ) }}
                        </th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
       {{-- Footer --}}
    </section>
</div>
{{-- AJAX Loader --}}
<div id="agentBookingAjaxLoader">
    <div class="agent-booking-loader-box">
        <div class="agent-booking-loader-spinner"></div>
        <b>
            Loading agent bookings...
        </b>
    </div>
</div>
@endsection