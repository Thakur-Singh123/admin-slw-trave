@extends('admin.layouts.master') 
@section('content')
<style>
/* Agent Booking Summary Cards */
.agent-booking-stats-grid {
    display: grid !important;
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: 16px !important;
    margin-bottom: 16px !important;
}
.agent-booking-stat-card {
    display: flex !important;
    min-width: 0 !important;
    align-items: center !important;
    gap: 14px !important;
    padding: 19px !important;
    background: var(--slw-white) !important;
    border: 1px solid var(--slw-border) !important;
    border-radius: var(--slw-radius) !important;
    box-shadow: var(--slw-shadow) !important;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease !important;
}
.agent-booking-stat-card:hover {
    box-shadow: 0 10px 28px rgba(16, 24, 44, 0.08) !important;
    transform: translateY(-2px) !important;
}
.agent-booking-stat-card .summary-icon {
    display: grid !important;
    width: 44px !important;
    height: 44px !important;
    flex: 0 0 44px !important;
    place-items: center !important;
    border-radius: 12px !important;
    font-size: 20px !important;
}
.agent-booking-stat-card p {
    margin: 0 0 5px !important;
    color: var(--slw-body) !important;
    font-size: 12px !important;
}

.agent-booking-stat-card h2 {
    margin: 0 !important;
    color: var(--slw-navy) !important;
    font-size: 23px !important;
    font-weight: 700 !important;
}
/*Same responsive */
@media (max-width: 1199.98px) {
    .agent-booking-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}
@media (max-width: 767.98px) {
    .agent-booking-stats-grid {
        gap: 10px !important;
    }
    .agent-booking-stat-card {
        gap: 10px !important;
        padding: 14px !important;
    }
    .agent-booking-stat-card .summary-icon {
        width: 38px !important;
        height: 38px !important;
        flex-basis: 38px !important;
        font-size: 17px !important;
    }
    .agent-booking-stat-card h2 {
        font-size: 19px !important;
    }
}
@media (max-width: 480px) {
    .agent-booking-stats-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
<div class="agent-page-content">
    <section class="page-heading">
        <div>
            <p>Agent Management</p>
            <h1>Agent List</h1>
            <span>View and manage all registered B2B agents.</span>
        </div>
        <!-- <button type="button" class="primary-button" data-bs-toggle="modal" data-bs-target="#addAgentModal"><i class="bi bi-person-plus"></i> Add New Agent</button> -->
    </section>
    <section class="agent-booking-stats-grid">
        <article class="agent-booking-stat-card">
            <span class="summary-icon blue">
                <i class="bi bi-people"></i>
            </span>
            <div>
                <p>Total Agents</p>
                <h2>{{ number_format($total_agents) }}</h2>
            </div>
        </article>
        <article class="agent-booking-stat-card">
            <span class="summary-icon green">
                <i class="bi bi-person-check"></i>
            </span>
            <div>
                <p>Active Agents</p>
                <h2>{{ number_format($active_agents) }}</h2>
            </div>
        </article>
        <article class="agent-booking-stat-card">
            <span class="summary-icon yellow">
                <i class="bi bi-hourglass-split"></i>
            </span>
            <div>
                <p>Pending Approval</p>
                <h2>{{ number_format($pending_agents) }}</h2>
            </div>
        </article>
        <article class="agent-booking-stat-card">
            <span class="summary-icon orange">
                <i class="bi bi-person-x"></i>
            </span>
            <div>
                <p>Inactive Agents</p>
                <h2>{{ number_format($inactive_agents) }}</h2>
            </div>
        </article>
    </section>
    <section class="content-card agent-list-card">
        <div class="agent-toolbar">
        <div class="agent-search-group">
            <div class="agent-search-box"><i class="bi bi-search"></i> <input type="search" id="agentSearch" placeholder="Search name or company..."></div>
            <div class="agent-search-box email-search-box"><i class="bi bi-envelope"></i> <input type="email" id="agentEmailSearch" placeholder="Search agent email..."></div>
        </div>
        <div class="agent-filters">
            <div class="date-filter-field">
                <label for="fromDate">From Date</label>
                <input
                    type="date"
                    id="fromDate"
                    class="form-control"
                    value="{{ request('from_date', now()->format('Y-m-d')) }}"
                >
            </div>

            <div class="date-filter-field">
                <label for="toDate">To Date</label>
                <input
                    type="date"
                    id="toDate"
                    class="form-control"
                    value="{{ request('to_date', now()->format('Y-m-d')) }}"
                >
            </div>
            <select class="form-select" id="statusFilter">
            <option value="all" {{ request('status', 'all') == 'all' ? 'selected' : '' }}>All Status</option>
            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Pending</option>
            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select class="form-select" id="countryFilter">
            <option value="all" {{ request('country', 'all') == 'all' ? 'selected' : '' }}>All Countries</option>
            @foreach($countries as $country)
            <option value="{{ strtolower($country) }}"
                {{ request('country') == strtolower($country) ? 'selected' : '' }}>{{ $country }}</option>
            @endforeach
            </select>
            <select class="form-select" id="amountFilter">
                <option value="all">All Amounts</option>
                <option value="low_high">Lowest to Highest</option>
                <option value="high_low">Highest to Lowest</option>
            </select>
            <a href="javascript:void(0)"
            class="clear-filter-button"
            id="clearAgentFilters">
                <i class="bi bi-arrow-counterclockwise"></i> Clear
            </a>
            <a href="{{ route('admin.agents.export') }}" class="export-button"><i class="bi bi-download"></i> Export</a>
        </div>
        </div>
        <div class="table-responsive">
        <table class="table agent-table align-middle mb-0" id="agentTable">
            <thead>
            <tr>
                <th><input type="checkbox" class="form-check-input" id="selectAllAgents"></th>
                <th>Agent</th>
                <th>Source</th>
                <th>Country</th>
                <th>Wallet Balance</th>
                <th>Total Bookings</th>
                <th>Status</th>
                <th>Registered</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($agents as $agent) 
            @php
                $wallet = $wallets[$agent->add_agent_id] ?? null;
                $balance = $wallet->balance ?? 0;
                $totalDeposit = $wallet->deposit ?? 0;
                $totalWithdrawal = $wallet->withdrawal ?? 0;

                $totalBookings = $bookings[$agent->add_agent_id] ?? 0;

                if ((int) $agent->isactive === 1) {
                    $status = 'active';
                } elseif ((int) $agent->isactive === 0) {
                    $status = 'inactive';
                } else {
                    $status = 'pending';
                }

                $initials = collect(explode(' ', $agent->name))
                    ->filter()
                    ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                    ->take(2)
                    ->implode('');
            @endphp
            <tr data-status="{{ $status }}"
            data-country="{{ strtolower($agent->country) }}"
            data-email="{{ $agent->email }}"
            data-registered="{{ $agent->date }}">
                <td><input type="checkbox" class="form-check-input agent-checkbox" value="{{ $agent->add_agent_id }}"></td>
                <td>
                <div class="agent-identity">
                    <span class="agent-avatar purple">{{ $initials }}</span>
                    <div><strong>{{ $agent->name }}</strong> <small>Agent ID: AGT-{{ $agent->add_agent_id }}</small><small>Email:-{{ $agent->email }}</small> <small>Pswrd:-{{ $agent->password }}</small></div>
                </div>
                </td>
                <td><strong>{{ $agent->contact_person }}</strong> <small>{{ $agent->source }}</small></td>
                <td><span class="country-name">{{ $agent->country ?? 'N/A' }}</span>
                    <small>City:-{{ $agent->city }}</small>
                    <small>State:-{{ $agent->state }}</small>
                    <small>Pin code:-{{ $agent->pincode }}</small>
                </td>
                @php
                    $balanceColor = $balance > 0
                        ? '#16a34a'  // Green
                        : ($balance == 0
                            ? '#ca8a04'  // Yellow
                            : '#dc2626'); // Red
                @endphp
                <td>
                    <strong class="wallet-amount-color" style="color: {{ $balanceColor }};">
                        Balance: ${{ number_format($balance, 2) }}
                        Deposit: ${{ number_format($totalDeposit, 2) }}
                        Withdrawal: ${{ number_format($totalWithdrawal, 2) }}
                    </strong>
                </td>
                <td><strong>{{ number_format($totalBookings) }}</strong></td>
                <td><span class="agent-status {{ $status }}">{{ ucfirst($status) }}</span></td>
                <td>@php 
                    $agentDate = $agent->date ?? null; 
                    if ($agentDate && strlen($agentDate) > 10 && $agentDate[10] === ':') { 
                        $agentDate = substr($agentDate, 0, 10) . ' ' . substr($agentDate, 11); 
                    } 
                    @endphp 
                    {{ $agentDate ? \Carbon\Carbon::parse($agentDate)->format('d M Y') : 'N/A' }}
                </td>
                <td>
                <div class="dropdown">
                    <button class="action-button"
                        data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ url('admin/agent-wallet', $agent->add_agent_id) }}"><i class="bi bi-eye"></i> View Details</a></li>
                    <!-- <li><a class="dropdown-item" href="#"><i class="bi bi-pencil"></i> Edit Agent</a></li> -->
                    <!-- <li><a class="dropdown-item" href="#"><i class="bi bi-wallet2"></i> Add Top-up</a></li> -->
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    @if((int) $agent->isactive === 1)
                    <li>
                        <a class="dropdown-item text-danger deactivate-agent"
                        href="javascript:void(0)"
                        data-id="{{ $agent->add_agent_id }}">
                            <i class="bi bi-person-x"></i> Deactivate
                        </a>
                    </li>
                    @else
                        <li>
                            <a class="dropdown-item text-success activate-agent"
                            href="javascript:void(0)"
                            data-id="{{ $agent->add_agent_id }}">
                                <i class="bi bi-person-check"></i> Activate
                            </a>
                        </li>

                    @endif
                    </ul>
                </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No agents found.</td>
            </tr>
            @endforelse
            </tbody>
        </table>
        </div>
        <div class="agent-table-footer">
        <p>Showing <strong>{{ $agents->firstItem() }}–{{ $agents->lastItem() }}</strong> of <strong>{{ $agents->total() }}</strong> agents</p>
        <nav aria-label="Agent pagination">
            <ul class="pagination pagination-sm mb-0">
            <li class="page-item {{ $agents->onFirstPage() ? 'disabled' : '' }}"><a class="page-link" href="{{ $agents->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a></li>
            <li class="page-item {{ $agents->currentPage() == 1 ? 'active' : '' }}"><a class="page-link" href="{{ $agents->url(1) }}">1</a></li>
            <li class="page-item {{ $agents->currentPage() == 2 ? 'active' : '' }}"><a class="page-link" href="{{ $agents->url(2) }}">2</a></li>
            <li class="page-item {{ $agents->currentPage() == 3 ? 'active' : '' }}"><a class="page-link" href="{{ $agents->url(3) }}">3</a></li>
            <li class="page-item"><button class="page-link" type="button">...</button></li>
            <li class="page-item {{ $agents->currentPage() == $agents->lastPage() ? 'active' : '' }}"><a class="page-link" href="{{ $agents->url($agents->lastPage()) }}">{{ $agents->lastPage() }}</a></li>
            <li class="page-item {{ !$agents->hasMorePages() ? 'disabled' : '' }}"><a class="page-link" href="{{ $agents->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a></li>
            </ul>
        </nav>
        </div>
    </section>
</div>
@endsection