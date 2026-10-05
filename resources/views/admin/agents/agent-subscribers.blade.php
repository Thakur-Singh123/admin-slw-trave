@extends('admin.layouts.master') 
@section('content') 
@include('admin.partials.notification')
<div class="subscriber-page-content">
  <nav class="page-breadcrumb"><a href="{{ route('admin.agents') }}">Agents</a> 
  <i class="bi bi-chevron-right"></i> <span>Subscriber List</span></nav>
  <section class="page-heading subscriber-page-heading">
    <div>
      <p>Agent Management</p>
      <h1>Agent Subscriber List</h1>
      <span>Manage agent plans, renewals, payments and subscription expiry.</span>
    </div>
    @if(strtolower(trim(auth()->user()->adm_privi ?? '')) === 'admin')
    <button type="button" 
        class="primary-button" 
        data-bs-toggle="modal" 
        data-bs-target="#addSubscriptionModal"><i class="bi bi-plus-lg"></i> Add Subscription</button>
    @endif
  </section>
  <section class="subscriber-summary-grid">
    <article>
      <span class="summary-icon blue"><i class="bi bi-people"></i></span>
      <div>
        <p>Total Subscribers</p>
        <h2 id="totalSubscribers">{{ $totalSubscribers }}</h2>
        <small>All subscription records</small>
      </div>
    </article>
    <article>
      <span class="summary-icon green"><i class="bi bi-patch-check"></i></span>
      <div>
        <p>Active Subscribers</p>
        <h2 id="activeSubscribers">{{ $activeSubscribers }}</h2>
        <small>{{ $totalSubscribers > 0 ? number_format(($activeSubscribers / $totalSubscribers) * 100, 1) : 0 }}% of total subscribers</small>
      </div>
    </article>
    <article>
      <span class="summary-icon yellow"><i class="bi bi-clock-history"></i></span>
      <div>
        <p>Expiring in 15 Days</p>
        <h2 id="expiringSubscribers">{{ $expiringSubscribers }}</h2>
        <small>Renewal reminder required</small>
      </div>
    </article>
    <article>
      <span class="summary-icon orange"><i class="bi bi-calendar-x"></i></span>
      <div>
        <p>Expired</p>
        <h2 id="expiredSubscribers">{{ $expiredSubscribers }}</h2>
        <small>Access renewal required</small>
      </div>
    </article>
  </section>
  <section class="content-card subscriber-list-card">
    <div class="subscriber-toolbar">
      <div class="subscriber-search"><i class="bi bi-search"></i> <input type="search"
                    id="subscriberSearch"
                    value="{{ request('search', '') }}"
                    placeholder="Search name, Agent ID, email or Subscription ID..."></div>
      <div class="subscriber-filters">
        <select class="form-select" id="subscriptionPlanFilter">
          <option value="all" {{ request('plan', 'all') === 'all' ? 'selected' : '' }}>All Plans</option>
          <option value="starter" {{ request('plan') === 'starter' ? 'selected' : '' }}>Starter</option>
          <option value="business" {{ request('plan') === 'business' ? 'selected' : '' }}>Business</option>
          <option value="premium" {{ request('plan') === 'premium' ? 'selected' : '' }}>Premium</option>
        </select>
        <select class="form-select" id="subscriptionStatusFilter">
          <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
          <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
          <option value="expiring" {{ request('status') === 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
          <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
          <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
        <div class="expiry-filter">
            <label for="subscriptionFromDate">From Date</label>
            <input
                type="date"
                class="form-control"
                id="subscriptionFromDate"
                value="{{ request('from_date', '') }}"
            >
        </div>
        <div class="expiry-filter">
            <label for="subscriptionToDate">To Date</label>
            <input
                type="date"
                class="form-control"
                id="subscriptionToDate"
                value="{{ request('to_date', '') }}"
            >
        </div>
        <button type="button"
                    class="filter-button"
                    id="resetSubscriptionFilter"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
        <button type="button"
                    class="filter-button"
                    id="exportSubscriberList"><i class="bi bi-download"></i> Export</button>
      </div>
    </div>
    <!-- <div class="subscriber-notice">
            <i class="bi bi-info-circle"></i>
            <p>
                <strong>Expiry tracking is enabled.</strong>
                Agents whose subscriptions expire within 15 days should receive a renewal reminder.
            </p>
            <button type="button">
                <i class="bi bi-envelope"></i>
                Send Reminders
            </button>
        </div> -->
    <div class="table-responsive">
      <table class="table subscriber-table align-middle mb-0" id="subscriberTable">
        <thead>
          <tr>
            <th>Subscription</th>
            <th>Agent</th>
            <th>Subscription Period</th>
            <th>Expiry</th>
            <th>Amount</th>
            <th>Payment</th>
            <th>Status</th>
            <!-- <th>Action</th> -->
          </tr>
        </thead>
        <tbody>
          @forelse($subscriptions as $subscription)
            @php
              $statusClass = strtolower($subscription['status'] ?? 'pending');
              $company = $subscription['company'] ?? 'N/A';
              $initials = collect(explode(' ', $company))
                  ->filter()
                  ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                  ->take(2)
                  ->implode('');

              $expiryDate = !empty($subscription['expiry_date'])
                  ? \Carbon\Carbon::parse($subscription['expiry_date'])
                  : null;

              $startDate = !empty($subscription['start_date'])
                  ? \Carbon\Carbon::parse($subscription['start_date'])
                  : null;

              $paymentStatus = strtolower($subscription['payment_status'] ?? 'pending');
            @endphp

          <tr data-plan="{{ strtolower($subscription['plan'] ?? '') }}"
              data-status="{{ $statusClass }}"
              data-expiry="{{ $expiryDate ? $expiryDate->format('Y-m-d') : '' }}"
              data-amount="{{ number_format($subscription['amount'] ?? 0, 2, '.', '') }}">

            {{-- Subscription --}}
            <td>
              <strong>{{ $subscription['id'] ?? 'N/A' }}</strong>
              <small>{{ $subscription['billing'] ?? 'N/A' }} subscription</small>
              <small style="display:block;">Payment ID: {{ $subscription['payment_id'] ?? 'N/A' }}</small>
              <small style="display:block;">Subscription ID: {{ $subscription['subscription_id'] ?? 'N/A' }}</small>
            </td>

            {{-- Agent --}}
            <td>
              <div class="subscriber-agent">
                <span>{{ $initials ?: 'AG' }}</span>
                <div>
                  <strong>{{ $company }}</strong>
                  <small>{{ $subscription['agent_id'] ?? 'N/A' }} · {{ $subscription['email'] ?? 'N/A' }}</small>
                  @php
                  $agentId = (int) str_replace(
                      'AGT-',
                      '',
                      $subscription['agent_id'] ?? 0
                  );

                  $leadCount = $agentLeadCounts[$agentId] ?? 0;
              @endphp

              <small style="color:#198754; font-weight:700;">
                  {{ $leadCount }} Leads
              </small>
                  </div>
                </div>
            </td>
            {{-- Subscription Period --}}
            <td>
              <strong>{{ $startDate ? $startDate->format('d M Y') : 'N/A' }}</strong>
              <small>to {{ $expiryDate ? $expiryDate->format('d M Y') : 'N/A' }}</small>
            </td>

            {{-- Expiry --}}
            <td>
              <strong>{{ $expiryDate ? $expiryDate->format('d M Y') : 'N/A' }}</strong>

              @if(($subscription['days_left'] ?? 0) > 0)
                <span class="days-left {{ ($subscription['days_left'] ?? 0) <= 15 ? 'warning' : '' }}">
                  {{ (int) $subscription['days_left'] }} days left
                </span>
              @else
                <span class="days-left expired">Expired</span>
              @endif
            </td>

            {{-- Payment Amount --}}
            <td>
              @php
                $currency = strtoupper($subscription['currency'] ?? 'USD');
                $symbol = match ($currency) {
                    'INR' => '₹',
                    'USD' => '$',
                    'THB' => '฿',
                    'EUR' => '€',
                    'GBP' => '£',
                    'AUD' => 'A$',
                    'CAD' => 'C$',
                    'SGD' => 'S$',
                    'AED' => 'د.إ',
                    default => $currency . ' ',
                };
              @endphp
              <strong class="subscription-amount">
                {{ $symbol }}{{ number_format($subscription['amount'] ?? 0, 2) }}
              </strong>
              <small>{{ $currency }}</small>
            </td>

              {{-- Payment Table Data --}}
              <td>
              <span class="payment-badge paid">
                  <i></i>
                  Paid
              </span>
            </td>

            {{-- Subscription Status --}}
            <td>
              <span class="subscription-status {{ $statusClass }}">
                <i></i>
                {{ $subscription['status'] ?? 'Pending' }}
              </span>
            </td>

            {{-- Action --}}
            <!-- <td>
              <div class="subscriber-actions">
                <a href="#" title="View"><i class="bi bi-eye"></i></a>
                <button type="button" title="Renew"><i class="bi bi-arrow-repeat"></i></button>
                <button type="button" title="More"><i class="bi bi-three-dots"></i></button>
              </div>
            </td> -->
          </tr>

          @empty
          <tr>
            <td colspan="8" class="text-center py-4">No payment records found.</td>
          </tr>
          @endforelse
        </tbody>

        <tfoot>
          <tr>
            <th colspan="4" style="text-align:right;">Total</th>
            <th>
              @php
                $currencyTotals = collect($subscriptions)
                    ->groupBy(fn($item) => strtoupper($item['currency'] ?? 'USD'))
                    ->map(fn($items) => $items->sum('amount'));
              @endphp

              @foreach($currencyTotals as $currency => $total)
                @php
                  $symbol = match ($currency) {
                      'INR' => '₹',
                      'USD' => '$',
                      'THB' => '฿',
                      'EUR' => '€',
                      'GBP' => '£',
                      'AUD' => 'A$',
                      'CAD' => 'C$',
                      'SGD' => 'S$',
                      'AED' => 'د.إ',
                      default => $currency . ' ',
                  };
                @endphp
               <strong class="subscription-amount" style="display:block; color:#172033 !important;">
                    {{ $symbol }}{{ number_format($total, 2) }}
                </strong>

                <small style="display:block; color:#172033 !important;">
                    {{ $currency }}
                </small>
              @endforeach
            </th>
            <th colspan="3"></th>
          </tr>
        </tfoot>
      </table>
      <div class="table-footer">
        <p>
            Showing
            <strong>{{ $paginator->firstItem() ?? 0 }} – {{ $paginator->lastItem() ?? 0 }}</strong>
            of
            <strong>{{ $paginator->total() }}</strong>
            subscribers
        </p>
        @if($paginator->total() > 20)
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                        @if($paginator->currentPage() > 1)
                            <a class="page-link"
                              href="{{ $paginator->previousPageUrl() }}">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        @else
                            <a class="page-link" href="#">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        @endif
                    </li>
                    @for($page = 1; $page <= $paginator->lastPage(); $page++)
                        <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                            <a class="page-link"
                              href="{{ $page == 1
                                  ? route('admin.agent.subscriptions')
                                  : request()->fullUrlWithQuery(['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        </li>
                    @endfor
                    <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                        @if($paginator->hasMorePages())
                            <a class="page-link"
                              href="{{ $paginator->nextPageUrl() }}">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @else
                            <a class="page-link" href="#">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @endif
                    </li>
                </ul>
            </nav>
        @endif
    </div>
    </div>
  </section>
</div>
<!-- Add Subscription Modal -->
<div class="modal fade"
    id="addSubscriptionModal"
    tabindex="-1"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content subscription-modal">
      <div class="modal-header">
        <div>
          <span><i class="bi bi-gem"></i></span>
          <div>
            <h5 class="modal-title">Add Agent Subscription</h5>
            <small>Create or renew an agent subscription.</small>
          </div>
        </div>
        <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('admin.agent.subscriptions.store') }}"
    method="POST">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Select Agent *</label>
              <select class="form-select"
                                name="agent_id" required>
                <option value="">Choose an agent...</option>
                @foreach($agents as $agent)
                <option value="{{ $agent->add_agent_id }}">AGT-{{ $agent->add_agent_id }} · {{ $agent->name }}</option>
                @endforeach
              </select>
              @error('agent_id')
              <label class="error">{{ $message }}</label>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Subscription Plan *</label>
              <select class="form-select"
                                name="plan"
                                id="modalSubscriptionPlan" required>
                <option value="">Choose plan...</option>
                <option value="Starter"
                                    data-monthly="19"
                                    data-yearly="190">Starter</option>
                <option value="Business"
                                    data-monthly="49"
                                    data-yearly="499">Business</option>
                <option value="Premium"
                                    data-monthly="79"
                                    data-yearly="799">Premium</option>
              </select>
              @error('plan')
              <label class="error">{{ $message }}</label>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Billing Cycle *</label>
              <select class="form-select"
                                name="billing"
                                id="modalBillingCycle" required>
                <option value="Monthly">Monthly</option>
                <option value="Yearly">Yearly</option>
              </select>
              @error('billing')
              <label class="error">{{ $message }}</label>
              @enderror
            </div>
            <div class="col-md-6"><label class="form-label">Start Date *</label> <input type="date"
                                class="form-control"
                                name="start_date"
                                id="subscriptionStartDate"
                                value="{{ now()->format('Y-m-d') }}" required> @error('start_date') <label class="error">{{ $message }}</label> @enderror</div>
            <div class="col-md-6"><label class="form-label">Expiry Date *</label> <input type="date"
                                class="form-control"
                                name="expiry_date"
                                id="subscriptionEndDate" required> @error('expiry_date') <label class="error">{{ $message }}</label> @enderror</div>
            <div class="col-md-6">
              <label class="form-label">Subscription Amount *</label>
              <div class="amount-input"><span>$</span> <input type="number"
                                    name="amount"
                                    id="subscriptionAmount"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00" required> <em>USD</em> @error('amount') <label class="error">{{ $message }}</label> @enderror</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Payment Status *</label>
              <select class="form-select"
                                name="payment_status"
                                required>
                <option value="Paid">Paid</option>
                <option value="Pending">Pending</option>
                <option value="Failed">Failed</option>
              </select>
              @error('payment_status')
              <label class="error">{{ $message }}</label>
              @enderror
            </div>
            <div class="col-12"><label class="form-label">Payment Reference</label> <input type="text"
                                class="form-control"
                                name="payment_reference"
                                placeholder="Transaction ID / Bank reference" required> @error('payment_reference') <label class="error">{{ $message }}</label> @enderror</div>
            <div class="col-12">
              <label class="auto-renew-option">
                <input type="checkbox"
                                    name="auto_renew"
                                    value="1">
                <span></span>
                <div><strong>Enable automatic renewal</strong> <small>Renew this subscription automatically at the end of the billing cycle.</small></div>
              </label>
            </div>
            <div class="col-12">
              <label class="form-label">Admin Note</label>
              <textarea class="form-control"
                                name="note"
                                rows="3"
                                placeholder="Optional subscription note..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button type="button"
                        class="cancel-button"
                        data-bs-dismiss="modal">Cancel</button> <button type="submit"
                        class="primary-button"><i class="bi bi-check-circle"></i> Save Subscription</button></div>
      </form>
    </div>
  </div>
</div>



@endsection