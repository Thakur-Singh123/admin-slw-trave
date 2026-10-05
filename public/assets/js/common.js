/* =========================================================
   SLW SUPER ADMIN - COMMON JAVASCRIPT
   Used on dashboard and all inner admin pages
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("adminSidebar");
  const sidebarOpen = document.getElementById("sidebarOpen");
  const sidebarClose = document.getElementById("sidebarClose");
  const sidebarOverlay = document.getElementById("sidebarOverlay");
  const searchInput = document.querySelector(".header-search input");

  /* Sidebar dropdown menus */
  document
    .querySelectorAll(".dropdown-toggle-button")
    .forEach(function (dropdownButton) {
      dropdownButton.addEventListener("click", function () {
        const currentMenuGroup = dropdownButton.closest(".menu-group");

        if (!currentMenuGroup) {
          return;
        }

        currentMenuGroup.classList.toggle("open");
      });
    });

  /* Open mobile sidebar */
  function openSidebar() {
    if (!sidebar || !sidebarOverlay) {
      return;
    }

    sidebar.classList.add("show");
    sidebarOverlay.classList.add("show");
    document.body.style.overflow = "hidden";
  }

  /* Close mobile sidebar */
  function closeSidebar() {
    if (!sidebar || !sidebarOverlay) {
      return;
    }

    sidebar.classList.remove("show");
    sidebarOverlay.classList.remove("show");
    document.body.style.overflow = "";
  }

  if (sidebarOpen) {
    sidebarOpen.addEventListener("click", openSidebar);
  }

  if (sidebarClose) {
    sidebarClose.addEventListener("click", closeSidebar);
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", closeSidebar);
  }

  /* Close sidebar after selecting a menu on mobile */
  document
    .querySelectorAll(".admin-sidebar a")
    .forEach(function (sidebarLink) {
      sidebarLink.addEventListener("click", function () {
        if (window.innerWidth < 992) {
          closeSidebar();
        }
      });
    });

  /* Close sidebar with Escape key */
  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeSidebar();
    }
  });

  /* Ctrl + K or Command + K focuses header search */
  document.addEventListener("keydown", function (event) {
    const isSearchShortcut =
      (event.ctrlKey || event.metaKey) &&
      event.key.toLowerCase() === "k";

    if (isSearchShortcut && searchInput) {
      event.preventDefault();
      searchInput.focus();
    }
  });

  /* Remove mobile sidebar state after resizing to desktop */
  window.addEventListener("resize", function () {
    if (window.innerWidth >= 992) {
      closeSidebar();
    }
  });
});