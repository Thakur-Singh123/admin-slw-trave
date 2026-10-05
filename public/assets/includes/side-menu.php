<?php
$activePage = $activePage ?? '';
?>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="admin-logo">
            <strong>SLW</strong>
            <small>ADMIN</small>
        </a>

        <button
            type="button"
            class="sidebar-close"
            id="sidebarClose"
            aria-label="Close menu"
        >
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="sidebar-menu">
        <p class="menu-heading">Overview</p>

        <a
            href="dashboard.php"
            class="menu-link <?= $activePage === 'dashboard' ? 'active' : ''; ?>"
        >
            <i class="bi bi-grid"></i>
            <span>Dashboard</span>
        </a>

        <p class="menu-heading">Management</p>

        <div class="menu-group <?= $activePage === 'finance' ? 'open' : ''; ?>">
            <button type="button" class="menu-link dropdown-toggle-button">
                <i class="bi bi-currency-dollar"></i>
                <span>Finance</span>
                <i class="bi bi-chevron-down menu-arrow"></i>
            </button>

            <div class="submenu">
                <a href="finance-report.php">Reports</a>
                <a href="all-bookings.php">All Bookings</a>
                <a href="agent-topups.php">Agent Top-ups</a>
                <a href="transactions.php">Transactions</a>
            </div>
        </div>

       <div class="menu-group <?= strpos($activePage, 'agent') === 0 ? 'open' : ''; ?>">
            <button type="button" class="menu-link dropdown-toggle-button">
                <i class="bi bi-people"></i>
                <span>Agents</span>
                <i class="bi bi-chevron-down menu-arrow"></i>
            </button>

            <div class="submenu">
                <a
                    href="agent-list.php"
                    class="<?= $activePage === 'agent-list' ? 'active' : ''; ?>"
                >
                    Agent List
                </a>

                <a
                    href="agent-topup.php"
                    class="<?= $activePage === 'agent-topup' ? 'active' : ''; ?>"
                >
                    Agent Top-up
                </a>

                <a
                    href="agent-report.php"
                    class="<?= $activePage === 'agent-report' ? 'active' : ''; ?>"
                >
                    Agent Reports
                </a>
            </div>
        </div>

        <div class="menu-group <?= strpos($activePage, 'supplier') === 0 ? 'open' : ''; ?>">
            <button type="button" class="menu-link dropdown-toggle-button">
                <i class="bi bi-buildings"></i>
                <span>Suppliers</span>
                <i class="bi bi-chevron-down menu-arrow"></i>
            </button>

            <div class="submenu">
                <a href="supplier-list.php">Supplier List</a>
                <a href="add-supplier.php">Add Supplier</a>
                <a href="supplier-report.php">Supplier Reports</a>
            </div>
        </div>

      <div class="menu-group <?= strpos($activePage, 'content') === 0 ? 'open' : ''; ?>">
            <button type="button" class="menu-link dropdown-toggle-button">
                <i class="bi bi-journal-text"></i>
                <span>Content</span>
                <i class="bi bi-chevron-down menu-arrow"></i>
            </button>

            <div class="submenu">
                <a href="add-content.php">Add Content</a>
                <a href="add-city.php">Add City</a>
                <a href="add-country.php">Add Country</a>
                <a href="add-faq.php">Add FAQ</a>
            </div>
        </div>

        <p class="menu-heading">System</p>

        <a
            href="settings.php"
            class="menu-link <?= $activePage === 'settings' ? 'active' : ''; ?>"
        >
            <i class="bi bi-gear"></i>
            <span>Settings</span>
        </a>
    </nav>

    <div class="sidebar-profile">
        <span class="profile-avatar">KS</span>

        <div class="profile-info">
            <strong>Krishna Sharma</strong>
            <small>Super Administrator</small>
        </div>

        <a href="logout.php" class="logout-button" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</aside>

<button
    type="button"
    class="sidebar-overlay"
    id="sidebarOverlay"
    aria-label="Close sidebar"
></button>
