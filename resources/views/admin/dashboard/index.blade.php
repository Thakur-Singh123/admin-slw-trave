@extends('admin.layouts.master')
@section('content')
@php
  $admin = auth()->user();
@endphp
<div class="dashboard-content">
  <section class="page-heading">
    <div>
      <p>{{ \Carbon\Carbon::now()->format('l, j F Y') }}</p>
      <h1>Good evening, {{ $admin->adm_name ?? 'Admin' }}</h1>
      <span>Here’s what’s happening across SLW Travel today.</span>
    </div>
    <!-- <button type="button" class="primary-button"><i class="bi bi-plus-lg"></i> Create Booking</button> -->
  </section>
  <section class="stats-grid">
    <article class="stat-card">
      <span class="stat-icon orange"><i class="bi bi-wallet2"></i></span>
      <div>
        <p>Total Revenue</p>
        <h2>${{ number_format((float) $total_revenue, 2) }}</h2>
        <small class="positive"><i class="bi bi-graph-up-arrow"></i> {{ number_format(abs($revenue_percentage), 1) }}%<span>vs last month</span></small>
      </div>
    </article>
    <article class="stat-card">
      <span class="stat-icon green"><i class="bi bi-clipboard-check"></i></span>
      <div>
        <p>Total Bookings</p>
        <h2>{{ number_format($total_bookings) }}</h2>
        <small class="positive"><i class="bi bi-graph-up-arrow"></i>{{ number_format(abs($percentage), 1) }}%<span>vs last month</span></small>
      </div>
    </article>
    <article class="stat-card">
      <span class="stat-icon blue"><i class="bi bi-people"></i></span>
      <div>
        <p>Total Agents</p>
        <h2>{{ number_format($total_agents) }}</h2>
        <small class="positive"><i class="bi bi-graph-up-arrow"></i>{{ number_format($agent_percentage, 1) }}% <span>vs last month</span></small>
      </div>
    </article>
    <article class="stat-card">
      <span class="stat-icon yellow"><i class="bi bi-buildings"></i></span>
      <div>
        <p>Total Suppliers</p>
        <h2>{{ number_format($total_suppliers) }}</h2>
        <small class="pending-text"><strong>{{ $pending_suppliers }}</strong> awaiting approval</small>
      </div>
    </article>
  </section>
  <section class="dashboard-grid">
    <article class="content-card revenue-card">
      <div class="card-heading">
        <div>
          <h3>Revenue Overview</h3>
          <p>Monthly revenue and agent top-ups</p>
        </div>
        <select class="form-select">
          <option>Last 6 months</option>
          <option>This year</option>
        </select>
      </div>
      <div class="chart-container">
        <div class="chart-labels"><span>$40k</span><span>$30k</span><span>$20k</span><span>$10k</span><span>$0</span></div>
        <div class="chart-bars" id="dashboardChart"></div>
      </div>
      <div class="chart-legend"><span><i class="revenue-dot"></i> Revenue</span><span><i class="topup-dot"></i> Top-ups</span></div>
    </article>
    <article class="content-card snapshot-card">
      <div class="card-heading">
        <div>
          <h3>Business Snapshot</h3>
          <p>Today’s live activity</p>
        </div>
        <a href="#">View Report</a>
      </div>
      <div class="snapshot-list">
        <div class="snapshot-item">
          <span class="snapshot-icon"><i class="bi bi-globe"></i></span>
          <p>Today’s Bookings<strong>{{ number_format($today_bookings) }}</strong></p>
          <span class="snapshot-badge success"> {{ $booking_percentage >= 0 ? '+' : '' }}{{ $booking_percentage }}%</span>
        </div>
        <div class="snapshot-item">
          <span class="snapshot-icon"><i class="bi bi-wallet2"></i></span>
          <p>Agent Top-ups<strong>
          ${{ number_format($today_agent_topups, 2) }}
          <small>Agents:- {{ $today_agent_count }}</small>
         </strong></p>
          <span class="snapshot-badge success">{{ $topup_percentage >= 0 ? '+' : '' }}{{ $topup_percentage }}%</span>
        </div>
        <div class="snapshot-item">
          <span class="snapshot-icon"><i class="bi bi-question-circle"></i></span>
          <p>Pending Requests<strong>{{ number_format($pending_requests) }}</strong></p>
          <span class="snapshot-badge warning">Needs Review</span>
        </div>
      </div>
    </article>
  </section>
  <section class="content-card booking-card">
    <div class="card-heading">
      <div>
        <h3>Recent Bookings</h3>
        <p>Latest bookings received across all channels</p>
      </div>
      <a href="{{ url('admin/bookings') }}">View All Bookings <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="table-responsive">
      <table class="table booking-table align-middle mb-0">
        <thead>
          <tr>
            <th>Booking ID</th>
            <th>Type</th>
            <th>Travel Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Service</th>
          </tr>
        </thead>
        <tbody>
          @foreach($latest_bookings as $booking)
          <tr>
            <td><strong>SLW {{ $booking->id  }}</strong></td>
            <td>{{ $booking->type ?? 'N/A' }}</td>
            <td>{{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('d M Y') : 'N/A' }}</td>
            <td><strong>{{ $booking->currency ?? '$' }}{{ number_format((float) ($booking->total_amount ?? 0), 2) }}</strong></td>
            <td>@php $status = strtolower(trim($booking->status ?? '')); @endphp @if($status === 'confirmed') <span class="status confirmed">Confirmed</span> @elseif($status === 'pending') <span class="status pending">Pending</span> @elseif($status === 'cancelled') <span class="status cancelled">Cancelled</span> @else <span class="status pending">{{ $booking->status ?? 'Pending' }}</span> @endif</td>
            <td>{{ \Illuminate\Support\Str::words($booking->item_name ?? 'N/A', 6, '...') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
@endsection