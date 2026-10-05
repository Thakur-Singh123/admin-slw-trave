<header class="admin-header">
  <button type="button" class="mobile-menu-button" id="sidebarOpen" aria-label="Open menu">
    <i class="bi bi-list"></i>
  </button>
  @php
    $admin = auth()->user();
  @endphp
  <div class="header-actions" style="margin-left: auto;">
    <div class="header-profile">
      <span class="profile-avatar">
          {{ strtoupper(substr($admin->adm_name ?? 'A', 0, 2)) }}
      </span>
      <div>
          <strong>{{ $admin->adm_name ?? 'Admin' }}</strong>
          <small>{{ ucfirst($admin->adm_privi ?? 'admin') }}</small>
      </div>
    </div>
  </div>
</header>