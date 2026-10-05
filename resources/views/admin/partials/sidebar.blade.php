<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-header">
    <a href="{{ url('admin/dashboard') }}" class="admin-logo">
        <strong>SLW</strong>
        <small>ADMIN</small>
    </a>
    <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close menu">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
  @php
    $privilege = strtolower(trim(auth()->user()->adm_privi ?? ''));

    /*Dashboard */
    $dashboardActive = request()->is('admin/dashboard');

    /*Finance*/
    $allBookingsActive =
      request()->is('admin/bookings') ||
      request()->is('admin/bookings/*');

    $agentSummaryActive =
      request()->is('admin/agent-booking-summary') ||
      request()->is('admin/agent-booking-summary/*');

    $sourceSummaryActive =
      request()->is('admin/source-summary') ||
      request()->is('admin/source-summary/*');

    $supplierPaymentActive =
      request()->is('admin/supplier-payments') ||
      request()->is('admin/supplier-payments/*');

    $financeOpen =
      $allBookingsActive ||
      $agentSummaryActive ||
      $sourceSummaryActive ||
      $supplierPaymentActive;


    /*Agents*/
    $agentListActive =
      request()->is('admin/agents') ||
      request()->is('admin/agents/*') ||
      request()->is('admin/agent-wallet/*');

    $agentBulkEmailActive =
      request()->is('admin/agent-bulk-email') ||
      request()->is('admin/agent-bulk-email/*');

    $agentTopupActive =
      request()->is('admin/agent-topup-report') ||
      request()->is('admin/agent-topup-report/*');

    $agentSubscriberActive =
      request()->is('admin/agent-subscribers') ||
      request()->is('admin/agent-subscribers/*');

    $leadsActive =
      request()->is('admin-inquires') ||
      request()->is('admin-inquires/*');

    $agentsOpen =
      $agentListActive ||
      $agentBulkEmailActive ||
      $agentTopupActive ||
      $agentSubscriberActive ||
      $leadsActive;

    /*Suppliers */
    $supplierOpen =
      request()->is('admin/suppliers') ||
      request()->is('admin/suppliers/*');

    /*Content*/
    $contentOpen =
      request()->is('admin/content') ||
      request()->is('admin/content/*') ||
      request()->is('admin/cities') ||
      request()->is('admin/cities/*') ||
      request()->is('admin/countries') ||
      request()->is('admin/countries/*') ||
      request()->is('admin/faqs') ||
      request()->is('admin/faqs/*');
  @endphp
  <nav class="sidebar-menu">
    <p class="menu-heading">Overview</p>
    {{-- Dashboard --}}
    <a href="{{ url('admin/dashboard') }}" class="menu-link {{ $dashboardActive ? 'active' : '' }}">
      <i class="bi bi-grid"></i>
      <span>Dashboard</span>
    </a>
    <p class="menu-heading">Management</p>
    <!--Admin-->
    @if($privilege === 'admin')
    <!--Finance-->
    <div class="menu-group {{ $financeOpen ? 'open' : '' }}">
      <button type="button" class="menu-link dropdown-toggle-button {{ $financeOpen ? 'active' : '' }}">
        <i class="bi bi-currency-dollar"></i>
        <span>Finance</span>
        <i class="bi bi-chevron-down menu-arrow"></i>
      </button>
      <div class="submenu">
        <a href="#">Reports </a>
        <a href="{{ url('admin/bookings') }}" class="{{ $allBookingsActive ? 'active' : '' }}">All Bookings</a>
        <a href="{{ url('admin/agent-booking-summary') }}" class="{{ $agentSummaryActive ? 'active' : '' }}">Agent Booking Summaries</a>
        <a href="#">Transactions</a>
        <a href="{{ url('admin/source-summary') }}" class="{{ $sourceSummaryActive ? 'active' : '' }}">Source Summaries</a>
        <a href="{{ url('admin/supplier-payments') }}" class="{{ $supplierPaymentActive ? 'active' : '' }}">Supplier Payments</a>
      </div>
    </div>
    <!--Agents-->
    <div class="menu-group {{ $agentsOpen ? 'open' : '' }}">
      <button type="button" class="menu-link dropdown-toggle-button {{ $agentsOpen ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span>Agents</span>
        <i class="bi bi-chevron-down menu-arrow"></i>
      </button>
      <div class="submenu">
        <a href="{{ url('admin/agents') }}" class="{{ $agentListActive ? 'active' : '' }}">Agent List</a>
        <a href="{{ url('admin/agent-bulk-email') }}" class="{{ $agentBulkEmailActive ? 'active' : '' }}">Agent Bulk-Email</a>
        <a href="{{ url('admin/agent-topup-report') }}" class="{{ $agentTopupActive ? 'active' : '' }}">Agent Topup Reports</a>
        <a href="{{ url('admin/agent-subscribers') }}" class="{{ $agentSubscriberActive ? 'active' : '' }}">Agent Subscribers</a>
        <a href="{{ url('admin-inquires') }}" class="{{ $leadsActive ? 'active' : '' }}">Agent Lead</a>
      </div>
    </div>
    <!--Suppliers-->
    <div class="menu-group {{ $supplierOpen ? 'open' : '' }}">
      <button type="button" class="menu-link dropdown-toggle-button {{ $supplierOpen ? 'active' : '' }}">
        <i class="bi bi-buildings"></i>
        <span>Suppliers</span>
        <i class="bi bi-chevron-down menu-arrow"></i>
      </button>
      <div class="submenu">
        <a href="#">Supplier List</a>
        <a href="#">Add Supplier</a>
        <a href="#">Supplier Reports</a>
      </div>
    </div>
    <!--Content-->
    <div class="menu-group {{ $contentOpen ? 'open' : '' }}">
      <button type="button" class="menu-link dropdown-toggle-button {{ $contentOpen ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i>
        <span>Content</span>
        <i class="bi bi-chevron-down menu-arrow"></i>
      </button>
      <div class="submenu">
        <a href="#">Add Content</a>
        <a href="#">Add City</a>
        <a href="#"> Add Country</a>
        <a href="#">Add FAQ</a>
      </div>
    </div>
    <!--Leads-->
    <!--<div class="menu-group">
      <a href="{{ url('admin-inquires') }}" class="menu-link {{ $leadsActive ? 'active' : '' }}">
        <i class="bi bi-person-lines-fill"></i>
        <span>Leads</span>
      </a>
    </div>-->
    <!--FINANCE-->
    @elseif($privilege === 'finance')
    <div class="menu-group {{ $financeOpen ? 'open' : '' }}">
      <button type="button" class="menu-link dropdown-toggle-button {{ $financeOpen ? 'active' : '' }}">
        <i class="bi bi-currency-dollar"></i>
        <span>Finance</span>
        <i class="bi bi-chevron-down menu-arrow"></i>
      </button>
      <div class="submenu">
        <a href="#">Reports</a>
        <a href="{{ url('admin/bookings') }}" class="{{ $allBookingsActive ? 'active' : '' }}"> All Bookings</a>
        <a href="{{ url('admin/agent-booking-summary') }}" class="{{ $agentSummaryActive ? 'active' : '' }}">Agent Booking Summaries</a>
        <a href="#">Transactions</a>
        <a href="{{ url('admin/source-summary') }}" class="{{ $sourceSummaryActive ? 'active' : '' }}">Source Summaries</a>
        <a href="{{ url('admin/supplier-payments') }}" class="{{ $supplierPaymentActive ? 'active' : '' }}">Supplier Payments</a>
      </div>
    </div>
    <!--AGENT-->
    @elseif($privilege === 'agent')
    <div class="menu-group {{ $agentsOpen ? 'open' : '' }}">
      <button type="button" class="menu-link dropdown-toggle-button {{ $agentsOpen ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span>Agents</span>
        <i class="bi bi-chevron-down menu-arrow"></i>
      </button>
      <div class="submenu">
        <a href="{{ url('admin/agents') }}" class="{{ $agentListActive ? 'active' : '' }}">Agent List</a>
        <a href="{{ url('admin/agent-bulk-email') }}" class="{{ $agentBulkEmailActive ? 'active' : '' }}">Agent Bulk-Email</a>
        <!--<a href="{{ url('admin/agents-export') }}">Agent Reports</a> -->
        <a href="{{ url('admin/agent-subscribers') }}"
            class="{{ $agentSubscriberActive ? 'active' : '' }}">
            Agent Subscribers
        </a>
        <a href="{{ url('admin-inquires') }}" class="{{ $leadsActive ? 'active' : '' }}">Agent Leads</a>
      </div>
    </div>
    <!-- <div class="menu-group">
      <a href="{{ url('admin-inquires') }}" class="menu-link {{ $leadsActive ? 'active' : '' }}">
        <i class="bi bi-person-lines-fill"></i>
        <span>Agent Leads</span>
      </a>
    </div> -->
    <!--SUPPLIER-->
    @elseif($privilege === 'suppllier')
    <div class="menu-group {{ $supplierOpen ? 'open' : '' }}">
      <button type="button" class="menu-link dropdown-toggle-button {{ $supplierOpen ? 'active' : '' }}">
        <i class="bi bi-buildings"></i>
        <span>Suppliers</span>
        <i class="bi bi-chevron-down menu-arrow"></i>
      </button>
      <div class="submenu">
        <a href="#">Supplier List </a>
        <a href="#">Add Supplier</a>
        <a href="#">Supplier Reports</a>
      </div>
    </div>
    <!--CONTENT-->
    @elseif($privilege === 'content')
    <div class="menu-group {{ $contentOpen ? 'open' : '' }}">
      <button type="button" class="menu-link dropdown-toggle-button {{ $contentOpen ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i>
        <span>Content</span>
        <i class="bi bi-chevron-down menu-arrow"></i>
      </button>
      <div class="submenu">
        <a href="#">Add Content</a>
        <a href="#">Add City</a>
        <a href="#">Add Country</a>
        <a href="#">Add FAQ</a>
      </div>
    </div>
    <!--OPERATIONS-->
    @elseif($privilege === 'operations')
    <div class="menu-group open">
      <button type="button" class="menu-link dropdown-toggle-button active">
        <i class="bi bi-gear"></i>
        <span>Operations</span>
        <i class="bi bi-chevron-down menu-arrow"></i>
      </button>
      <div class="submenu">
        <a href="#">Operations List</a>
        <a href="#">Operations Reports</a>
      </div>
    </div>
    <!--OTHER-->
    @elseif($privilege === 'other')
    <div class="menu-group open">
      <button type="button" class="menu-link dropdown-toggle-button active">
        <i class="bi bi-grid"></i>
        <span>Other</span>
        <i class="bi bi-chevron-down menu-arrow"></i>
      </button>
      <div class="submenu">
        <a href="#">Other Dashboard</a>
      </div>
    </div>
    @endif
    <p class="menu-heading">System</p>
    @if($privilege === 'admin')
      <a href="#" class="menu-link">
          <i class="bi bi-gear"></i>
          <span>Settings</span>
      </a>
    @endif
  </nav>
  <!--Profile-->
  <div class="sidebar-profile">
    <span class="profile-avatar">
      {{ strtoupper(substr(auth()->user()->adm_name ?? 'SU', 0, 2)) }}
    </span>
    <div class="profile-info">
      <strong>
        {{ auth()->user()->adm_name ?? 'Admin' }}
      </strong>
      <small>
        {{ ucfirst($privilege) }}
      </small>
    </div>
    <a href="{{ route('logout') }}"
      class="logout-button"
      title="Logout"
      onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      <i class="bi bi-box-arrow-right"></i>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
      @csrf
    </form>
  </div>
</aside>
<style>
/*Active main menu - same as Dashboard */
.admin-sidebar .menu-group > .dropdown-toggle-button.active {
  background: #ff654f !important;
  color: #fff !important;
  border-radius: 12px !important;
}
.admin-sidebar .menu-group > .dropdown-toggle-button.active i {
  color: #fff !important;
}
.admin-sidebar .menu-group > .dropdown-toggle-button.active .menu-arrow {
  color: #fff !important;
}
</style>