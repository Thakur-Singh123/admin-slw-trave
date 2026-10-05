/* =========================================================
   SLW SUPER ADMIN - ALL BOOKINGS PAGE
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const bookingTable = document.getElementById("bookingTable");
    const tableBody = bookingTable ? bookingTable.querySelector("tbody") : null;
    const bookingRows = tableBody
        ? Array.from(tableBody.querySelectorAll("tr"))
        : [];

    const globalSearch = document.getElementById("globalSearch");
    const bookingSearch = document.getElementById("bookingSearch");
    const typeTabs = Array.from(
        document.querySelectorAll("#bookingTypeTabs button")
    );
    const showFiltersButton = document.getElementById("showBookingFilters");
    const filterPanel = document.getElementById("bookingFilterPanel");
    const activeFilterCount = document.getElementById("activeFilterCount");
    const bookingFromDate = document.getElementById("bookingFromDate");
    const bookingToDate = document.getElementById("bookingToDate");
    const travelFromDate = document.getElementById("travelFromDate");
    const travelToDate = document.getElementById("travelToDate");
    const statusFilter = document.getElementById("bookingStatusFilter");
    const sourceFilter = document.getElementById("bookingSourceFilter");
    const applyFilterButton = document.getElementById("applyBookingFilter");
    const resetFilterButton = document.getElementById("resetBookingFilter");
    const exportButton = document.getElementById("exportBookings");
    const pageLength = document.getElementById("bookingPageLength");
    const resultCount = document.getElementById("bookingResultCount");
    const visibleBookingCount = document.getElementById("visibleBookingCount");
    const visibleBookingTotal = document.getElementById("visibleBookingTotal");

    let selectedBookingType = "all";
    let emptyResultRow = null;

    function normalizeText(value) {
        return String(value || "").trim().toLowerCase();
    }

    function formatAmount(amount) {
        return Number(amount || 0).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function showMessage(message, type) {
        const previousAlert = document.querySelector(".booking-page-alert");

        if (previousAlert) {
            previousAlert.remove();
        }

        const isSuccess = type === "success";
        const alertBox = document.createElement("div");

        alertBox.className =
            "alert alert-" +
            (isSuccess ? "success" : "danger") +
            " booking-page-alert d-flex align-items-center gap-2";
        alertBox.setAttribute("role", "alert");
        alertBox.innerHTML =
            '<i class="bi bi-' +
            (isSuccess ? "check-circle" : "exclamation-circle") +
            '"></i><span>' +
            message +
            "</span>";

        const pageHeading = document.querySelector(".booking-page-heading");

        if (pageHeading) {
            pageHeading.insertAdjacentElement("afterend", alertBox);
        }

        window.setTimeout(function () {
            if (alertBox.isConnected) {
                alertBox.remove();
            }
        }, 4500);
    }

    function createEmptyResultRow() {
        if (!tableBody || emptyResultRow) {
            return;
        }

        emptyResultRow = document.createElement("tr");
        emptyResultRow.className = "booking-empty-row";
        emptyResultRow.innerHTML =
            '<td colspan="8" class="text-center py-5">' +
            '<i class="bi bi-search d-block fs-3 text-secondary mb-2"></i>' +
            '<strong class="d-block">No booking found</strong>' +
            '<small class="d-block mt-1">Try changing the booking type, search or filters.</small>' +
            "</td>";
        emptyResultRow.hidden = true;
        tableBody.appendChild(emptyResultRow);
    }

    function isWithinDateRange(date, fromDate, toDate) {
        if (!date) {
            return true;
        }

        if (fromDate && date < fromDate) {
            return false;
        }

        if (toDate && date > toDate) {
            return false;
        }

        return true;
    }

    function getRowCurrency(row) {
        const amountElement = row.querySelector(".booking-amount");

        if (!amountElement) {
            return "USD";
        }

        return amountElement.textContent.trim().split(/\s+/)[0] || "USD";
    }

    function updateCurrencyTotal(filteredRows) {
        const currencyTotals = {};

        filteredRows.forEach(function (row) {
            const currency = getRowCurrency(row);
            const amount = Number.parseFloat(row.dataset.amount || "0");

            currencyTotals[currency] = (currencyTotals[currency] || 0) + amount;
        });

        const totalParts = Object.keys(currencyTotals).map(function (currency) {
            return currency + " " + formatAmount(currencyTotals[currency]);
        });

        visibleBookingTotal.textContent =
            totalParts.length > 0 ? totalParts.join(" Â· ") : "0.00";
        visibleBookingCount.textContent =
            filteredRows.length +
            (filteredRows.length === 1 ? " booking" : " bookings");
    }

    function countActiveFilters() {
        let count = 0;

        if (selectedBookingType !== "all") count += 1;
        if (bookingFromDate.value) count += 1;
        if (bookingToDate.value) count += 1;
        if (travelFromDate.value) count += 1;
        if (travelToDate.value) count += 1;
        if (statusFilter.value !== "all") count += 1;
        if (sourceFilter.value !== "all") count += 1;

        activeFilterCount.textContent = count;
        activeFilterCount.hidden = count === 0;
    }

    function validateDateRanges() {
        if (
            bookingFromDate.value &&
            bookingToDate.value &&
            bookingFromDate.value > bookingToDate.value
        ) {
            showMessage(
                "Booking To date must be the same as or later than Booking From date.",
                "error"
            );
            return false;
        }

        if (
            travelFromDate.value &&
            travelToDate.value &&
            travelFromDate.value > travelToDate.value
        ) {
            showMessage(
                "Travel To date must be the same as or later than Travel From date.",
                "error"
            );
            return false;
        }

        return true;
    }

    function applyFilters() {
        const searchValue = normalizeText(bookingSearch.value);
        const filteredRows = [];

        bookingRows.forEach(function (row) {
            const matchesType =
                selectedBookingType === "all" ||
                row.dataset.type === selectedBookingType;
            const matchesSearch =
                searchValue === "" ||
                normalizeText(row.textContent).includes(searchValue);
            const matchesStatus =
                statusFilter.value === "all" ||
                row.dataset.status === statusFilter.value;
            const matchesSource =
                sourceFilter.value === "all" ||
                row.dataset.source === sourceFilter.value;
            const matchesBookingDate = isWithinDateRange(
                row.dataset.bookingDate,
                bookingFromDate.value,
                bookingToDate.value
            );
            const matchesTravelDate = isWithinDateRange(
                row.dataset.travelDate,
                travelFromDate.value,
                travelToDate.value
            );

            const matchesAll =
                matchesType &&
                matchesSearch &&
                matchesStatus &&
                matchesSource &&
                matchesBookingDate &&
                matchesTravelDate;

            if (matchesAll) {
                filteredRows.push(row);
            }
        });

        const maximumRows = Number.parseInt(pageLength.value, 10) || 10;

        bookingRows.forEach(function (row) {
            const filteredIndex = filteredRows.indexOf(row);
            row.hidden = filteredIndex === -1 || filteredIndex >= maximumRows;
        });

        emptyResultRow.hidden = filteredRows.length !== 0;

        const displayedCount = Math.min(filteredRows.length, maximumRows);
        resultCount.innerHTML =
            "Showing <strong>" +
            displayedCount +
            "</strong> of <strong>" +
            filteredRows.length +
            "</strong> filtered bookings";

        updateCurrencyTotal(filteredRows);
        countActiveFilters();
    }

    function selectBookingType(button) {
        typeTabs.forEach(function (tab) {
            tab.classList.remove("active");
        });

        button.classList.add("active");
        selectedBookingType = button.dataset.type || "all";
        applyFilters();
    }

    function resetFilters() {
        selectedBookingType = "all";
        bookingSearch.value = "";
        bookingFromDate.value = "";
        bookingToDate.value = "";
        travelFromDate.value = "";
        travelToDate.value = "";
        statusFilter.value = "all";
        sourceFilter.value = "all";

        if (globalSearch) {
            globalSearch.value = "";
        }

        typeTabs.forEach(function (tab) {
            tab.classList.toggle("active", tab.dataset.type === "all");
        });

        applyFilters();
        showMessage("All booking filters have been reset.", "success");
    }

    function escapeCsvValue(value) {
        const cleanValue = String(value || "").replace(/\s+/g, " ").trim();
        return '"' + cleanValue.replace(/"/g, '""') + '"';
    }

    function exportFilteredBookings() {
        const filteredRows = bookingRows.filter(function (row) {
            const matchesType =
                selectedBookingType === "all" ||
                row.dataset.type === selectedBookingType;
            const matchesSearch =
                normalizeText(bookingSearch.value) === "" ||
                normalizeText(row.textContent).includes(
                    normalizeText(bookingSearch.value)
                );
            const matchesStatus =
                statusFilter.value === "all" ||
                row.dataset.status === statusFilter.value;
            const matchesSource =
                sourceFilter.value === "all" ||
                row.dataset.source === sourceFilter.value;
            const matchesBookingDate = isWithinDateRange(
                row.dataset.bookingDate,
                bookingFromDate.value,
                bookingToDate.value
            );
            const matchesTravelDate = isWithinDateRange(
                row.dataset.travelDate,
                travelFromDate.value,
                travelToDate.value
            );

            return (
                matchesType &&
                matchesSearch &&
                matchesStatus &&
                matchesSource &&
                matchesBookingDate &&
                matchesTravelDate
            );
        });

        if (filteredRows.length === 0) {
            showMessage("No filtered booking data is available to export.", "error");
            return;
        }

        const csvRows = [];
        const headings = Array.from(bookingTable.querySelectorAll("thead th"));

        csvRows.push(
            headings
                .slice(0, -1)
                .map(function (heading) {
                    return escapeCsvValue(heading.textContent);
                })
                .join(",")
        );

        filteredRows.forEach(function (row) {
            const cells = Array.from(row.querySelectorAll("td")).slice(0, -1);
            csvRows.push(
                cells
                    .map(function (cell) {
                        return escapeCsvValue(cell.textContent);
                    })
                    .join(",")
            );
        });

        const csvFile = new Blob(["\uFEFF" + csvRows.join("\n")], {
            type: "text/csv;charset=utf-8;"
        });
        const downloadUrl = URL.createObjectURL(csvFile);
        const downloadLink = document.createElement("a");

        downloadLink.href = downloadUrl;
        downloadLink.download =
            "slw-bookings-" + new Date().toISOString().slice(0, 10) + ".csv";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        downloadLink.remove();
        URL.revokeObjectURL(downloadUrl);

        showMessage(
            filteredRows.length + " booking records exported successfully.",
            "success"
        );
    }

    createEmptyResultRow();

    typeTabs.forEach(function (button) {
        button.addEventListener("click", function () {
            selectBookingType(button);
        });
    });

    showFiltersButton.addEventListener("click", function () {
        filterPanel.classList.toggle("open");
        showFiltersButton.classList.toggle("active");
    });

    bookingSearch.addEventListener("input", applyFilters);
    applyFilterButton.addEventListener("click", function () {
        if (!validateDateRanges()) {
            return;
        }

        applyFilters();
        showMessage("Booking filters applied successfully.", "success");
    });
    resetFilterButton.addEventListener("click", resetFilters);
    pageLength.addEventListener("change", applyFilters);
    exportButton.addEventListener("click", exportFilteredBookings);

    if (globalSearch) {
        globalSearch.addEventListener("input", function () {
            bookingSearch.value = globalSearch.value;
            applyFilters();
        });
    }

    document.addEventListener("keydown", function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
            event.preventDefault();
            bookingSearch.focus();
            bookingSearch.select();
        }
    });

    applyFilters();
});