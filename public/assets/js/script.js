document.addEventListener("DOMContentLoaded", function () {
  const loginPage = document.getElementById("loginPage");
  const dashboardPage = document.getElementById("dashboardPage");
  const loginForm = document.getElementById("loginForm");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");
  const openMenu = document.getElementById("openMenu");
  const closeMenu = document.getElementById("closeMenu");
  const password = document.getElementById("password");
  const showPassword = document.getElementById("showPassword");
  const pageTitle = document.getElementById("pageTitle");
  const pageSubtitle = document.getElementById("pageSubtitle");

  /*
   * Login
   */
  function showDashboard() {
    loginPage.classList.add("d-none");
    dashboardPage.classList.remove("d-none");

    window.location.hash = "dashboard";
  }

  loginForm.addEventListener("submit", function (event) {
    event.preventDefault();
    showDashboard();
  });

  /*
   * Password show/hide
   */
  showPassword.addEventListener("click", function () {
    const isVisible = password.type === "text";

    password.type = isVisible ? "password" : "text";
    showPassword.textContent = isVisible ? "Show" : "Hide";
  });

  /*
   * Sidebar dropdown menus
   */
  document.querySelectorAll(".nav-dropdown").forEach(function (button) {
    button.addEventListener("click", function () {
      button.classList.toggle("expanded");
      button.nextElementSibling.classList.toggle("open");
    });
  });

  /*
   * Sidebar submenu selection
   */
  document.querySelectorAll(".submenu button").forEach(function (button) {
    button.addEventListener("click", function () {
      document
        .querySelectorAll(".submenu button")
        .forEach(function (item) {
          item.classList.remove("selected");
        });

      button.classList.add("selected");

      const menuName = button.textContent.trim();

      pageTitle.textContent = menuName;
      pageSubtitle.textContent =
        "Manage " + menuName.toLowerCase() + " from this workspace.";

      closeMobileSidebar();
    });
  });

  /*
   * Mobile sidebar
   */
  function openMobileSidebar() {
    sidebar.classList.add("show");
    overlay.classList.remove("d-none");
    document.body.style.overflow = "hidden";
  }

  function closeMobileSidebar() {
    sidebar.classList.remove("show");
    overlay.classList.add("d-none");
    document.body.style.overflow = "";
  }

  openMenu.addEventListener("click", openMobileSidebar);
  closeMenu.addEventListener("click", closeMobileSidebar);
  overlay.addEventListener("click", closeMobileSidebar);

  /*
   * Revenue chart
   */
  const chartData = [
    {
      month: "Apr",
      revenue: 42,
      topup: 20
    },
    {
      month: "May",
      revenue: 55,
      topup: 33
    },
    {
      month: "Jun",
      revenue: 48,
      topup: 26
    },
    {
      month: "Jul",
      revenue: 73,
      topup: 51
    },
    {
      month: "Aug",
      revenue: 64,
      topup: 42
    },
    {
      month: "Sep",
      revenue: 88,
      topup: 66
    }
  ];

  const chartBars = document.getElementById("chartBars");

  chartBars.innerHTML = chartData
    .map(function (item) {
      return `
        <div class="bar-wrap">
          <div class="bar-stack">
            <i style="height: ${item.revenue}%"></i>
            <b style="height: ${item.topup}%"></b>
          </div>

          <span>${item.month}</span>
        </div>
      `;
    })
    .join("");

  /*
   * Search shortcut: Ctrl + K
   */
  document.addEventListener("keydown", function (event) {
    if (
      (event.ctrlKey || event.metaKey) &&
      event.key.toLowerCase() === "k"
    ) {
      event.preventDefault();

      document.querySelector(".global-search input").focus();
    }
  });

  /*
   * Keep dashboard open after page refresh
   */
  if (window.location.hash === "#dashboard") {
    showDashboard();
  }
});