/* =========================================================
   SLW SUPER ADMIN - AGENT LIST JAVASCRIPT
   Used only on agent-list.php
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
  const agentTable = document.getElementById("agentTable");
  const agentSearch = document.getElementById("agentSearch");
  const agentEmailSearch = document.getElementById("agentEmailSearch");
  const globalSearch = document.getElementById("globalSearch");
  const statusFilter = document.getElementById("statusFilter");
  const countryFilter = document.getElementById("countryFilter");
  const fromDate = document.getElementById("fromDate");
  const toDate = document.getElementById("toDate");
  const clearAgentFilters = document.getElementById("clearAgentFilters");
  const selectAllAgents = document.getElementById("selectAllAgents");
  const exportButton = document.querySelector(".export-button");

  if (!agentTable) {
    return;
  }

  const tableBody = agentTable.querySelector("tbody");
  const agentRows = Array.from(tableBody.querySelectorAll("tr[data-status]"));

  /* Search agents and apply dropdown filters */
  function filterAgents() {
    const searchText = agentSearch.value.trim().toLowerCase();
    const emailText = agentEmailSearch.value.trim().toLowerCase();
    const selectedStatus = statusFilter.value;
    const selectedCountry = countryFilter.value;
    const selectedFromDate = fromDate.value;
    const selectedToDate = toDate.value;

    let visibleAgentCount = 0;

    removeEmptyState();

    agentRows.forEach(function (row) {
      const rowText = row.textContent.toLowerCase();
      const rowStatus = row.dataset.status;
      const rowCountry = row.dataset.country;
      const rowEmail = row.dataset.email.toLowerCase();
      const registeredDate = row.dataset.registered;

      const matchesSearch = rowText.includes(searchText);
      const matchesEmail = rowEmail.includes(emailText);
      const matchesStatus =
        selectedStatus === "all" || rowStatus === selectedStatus;
      const matchesCountry =
        selectedCountry === "all" || rowCountry === selectedCountry;
      const matchesFromDate =
        selectedFromDate === "" || registeredDate >= selectedFromDate;
      const matchesToDate =
        selectedToDate === "" || registeredDate <= selectedToDate;

      const shouldDisplay =
        matchesSearch &&
        matchesEmail &&
        matchesStatus &&
        matchesCountry &&
        matchesFromDate &&
        matchesToDate;

      row.style.display = shouldDisplay ? "" : "none";

      if (shouldDisplay) {
        visibleAgentCount += 1;
      }
    });

    if (visibleAgentCount === 0) {
      displayEmptyState();
    }

    updateSelectAllCheckbox();
  }

  function displayEmptyState() {
    const emptyRow = document.createElement("tr");

    emptyRow.className = "agent-empty-row";
    emptyRow.innerHTML = `
      <td colspan="9">
        <div class="agent-empty-state">
          <i class="bi bi-search"></i>
          <strong>No agents found</strong>
          <span>Try changing the search text or selected filters.</span>
        </div>
      </td>
    `;

    tableBody.appendChild(emptyRow);
  }

  function removeEmptyState() {
    const emptyRow = tableBody.querySelector(".agent-empty-row");

    if (emptyRow) {
      emptyRow.remove();
    }
  }

  agentSearch.addEventListener("input", filterAgents);
  agentEmailSearch.addEventListener("input", filterAgents);
  statusFilter.addEventListener("change", filterAgents);
  countryFilter.addEventListener("change", filterAgents);
  fromDate.addEventListener("change", function () {
    if (toDate.value && fromDate.value > toDate.value) {
      toDate.value = fromDate.value;
    }
    filterAgents();
  });
  toDate.addEventListener("change", function () {
    if (fromDate.value && toDate.value < fromDate.value) {
      fromDate.value = toDate.value;
    }
    filterAgents();
  });

  clearAgentFilters.addEventListener("click", function () {
    agentSearch.value = "";
    agentEmailSearch.value = "";
    statusFilter.value = "all";
    countryFilter.value = "all";
    fromDate.value = "";
    toDate.value = "";

    if (globalSearch) {
      globalSearch.value = "";
    }

    filterAgents();
  });

  /* Connect header search with agent search */
  if (globalSearch) {
    globalSearch.addEventListener("input", function () {
      agentSearch.value = globalSearch.value;
      filterAgents();
    });
  }

  /* Select all visible agents */
  selectAllAgents.addEventListener("change", function () {
    getVisibleRows().forEach(function (row) {
      const checkbox = row.querySelector(".agent-checkbox");

      checkbox.checked = selectAllAgents.checked;
      updateSelectedRow(row, checkbox.checked);
    });
  });

  /* Individual agent checkbox */
  agentRows.forEach(function (row) {
    const checkbox = row.querySelector(".agent-checkbox");

    checkbox.addEventListener("change", function () {
      updateSelectedRow(row, checkbox.checked);
      updateSelectAllCheckbox();
    });
  });

  function updateSelectedRow(row, isSelected) {
    row.classList.toggle("selected-row", isSelected);
  }

  function getVisibleRows() {
    return agentRows.filter(function (row) {
      return row.style.display !== "none";
    });
  }

  function updateSelectAllCheckbox() {
    const visibleRows = getVisibleRows();
    const selectedRows = visibleRows.filter(function (row) {
      return row.querySelector(".agent-checkbox").checked;
    });

    selectAllAgents.checked =
      visibleRows.length > 0 && selectedRows.length === visibleRows.length;

    selectAllAgents.indeterminate =
      selectedRows.length > 0 && selectedRows.length < visibleRows.length;
  }

  /* Export currently visible agents as CSV */
  if (exportButton) {
    exportButton.addEventListener("click", function () {
      const visibleRows = getVisibleRows();

      if (visibleRows.length === 0) {
        window.alert("No agent data is available to export.");
        return;
      }

      const csvRows = [
        [
          "Company",
          "Agent ID",
          "Contact Person",
          "Email",
          "Country",
          "Wallet Balance",
          "Total Bookings",
          "Status",
          "Registered Date"
        ]
      ];

      visibleRows.forEach(function (row) {
        const cells = row.querySelectorAll("td");
        const company = cells[1].querySelector("strong").textContent.trim();
        const agentId = cells[1].querySelector("small").textContent.trim();
        const contactPerson = cells[2].querySelector("strong").textContent.trim();
        const email = cells[2].querySelector("small").textContent.trim();

        csvRows.push([
          company,
          agentId,
          contactPerson,
          email,
          cells[3].textContent.trim(),
          cells[4].textContent.trim(),
          cells[5].textContent.trim(),
          cells[6].textContent.trim(),
          cells[7].textContent.trim()
        ]);
      });

      const csvContent = csvRows
        .map(function (row) {
          return row
            .map(function (value) {
              return `"${String(value).replaceAll('"', '""')}"`;
            })
            .join(",");
        })
        .join("\n");

      downloadCsv(csvContent);
    });
  }

  function downloadCsv(csvContent) {
    const csvBlob = new Blob([csvContent], {
      type: "text/csv;charset=utf-8;"
    });

    const downloadUrl = URL.createObjectURL(csvBlob);
    const downloadLink = document.createElement("a");

    downloadLink.href = downloadUrl;
    downloadLink.download = "slw-agent-list.csv";

    document.body.appendChild(downloadLink);
    downloadLink.click();
    downloadLink.remove();

    URL.revokeObjectURL(downloadUrl);
  }
});