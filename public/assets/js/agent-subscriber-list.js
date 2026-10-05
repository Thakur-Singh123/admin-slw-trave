/* =========================================================
   SLW SUPER ADMIN - AGENT SUBSCRIBER LIST
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const subscriberTable = document.getElementById("subscriberTable");
    const tableBody = subscriberTable ? subscriberTable.querySelector("tbody") : null;
    const subscriberRows = tableBody
        ? Array.from(tableBody.querySelectorAll("tr"))
        : [];

    const globalSearch = document.getElementById("globalSearch");
    const subscriberSearch = document.getElementById("subscriberSearch");
    const planFilter = document.getElementById("subscriptionPlanFilter");
    const statusFilter = document.getElementById("subscriptionStatusFilter");
    const expiryFilter = document.getElementById("subscriptionExpiryDate");
    const resetFilterButton = document.getElementById("resetSubscriptionFilter");
    const exportButton = document.getElementById("exportSubscriberList");
    const resultCount = document.getElementById("subscriberResultCount");
    const reminderButton = document.querySelector(".subscriber-notice button");

    const subscriptionModal = document.getElementById("addSubscriptionModal");
    const subscriptionForm = document.getElementById("addSubscriptionForm");
    const planSelect = document.getElementById("modalSubscriptionPlan");
    const billingSelect = document.getElementById("modalBillingCycle");
    const startDateInput = document.getElementById("subscriptionStartDate");
    const endDateInput = document.getElementById("subscriptionEndDate");
    const amountInput = document.getElementById("subscriptionAmount");

    let emptyResultRow = null;

    function normalizeText(value) {
        return String(value || "").trim().toLowerCase();
    }

    function escapeHtml(value) {
        const element = document.createElement("div");
        element.textContent = String(value || "");
        return element.innerHTML;
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: "USD",
            minimumFractionDigits: 2
        }).format(Number(amount || 0));
    }

    function formatDisplayDate(dateValue) {
        if (!dateValue) {
            return "â€”";
        }

        const dateParts = dateValue.split("-");
        const date = new Date(
            Number(dateParts[0]),
            Number(dateParts[1]) - 1,
            Number(dateParts[2])
        );

        return new Intl.DateTimeFormat("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric"
        }).format(date);
    }

    function showMessage(message, type) {
        const previousAlert = document.querySelector(".subscriber-page-alert");

        if (previousAlert) {
            previousAlert.remove();
        }

        const isSuccess = type === "success";
        const alertBox = document.createElement("div");

        alertBox.className =
            "alert alert-" +
            (isSuccess ? "success" : "danger") +
            " subscriber-page-alert d-flex align-items-center gap-2";
        alertBox.setAttribute("role", "alert");
        alertBox.innerHTML =
            '<i class="bi bi-' +
            (isSuccess ? "check-circle" : "exclamation-circle") +
            '"></i><span>' +
            message +
            "</span>";

        const pageHeading = document.querySelector(".subscriber-page-heading");

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
        emptyResultRow.className = "subscriber-empty-row";
        emptyResultRow.innerHTML =
            '<td colspan="10" class="text-center py-5">' +
            '<i class="bi bi-search d-block fs-3 text-secondary mb-2"></i>' +
            '<strong class="d-block">No subscriber found</strong>' +
            '<small class="d-block mt-1">Try changing your search or filter selection.</small>' +
            "</td>";
        emptyResultRow.hidden = true;
        tableBody.appendChild(emptyResultRow);
    }

    function applyFilters() {
        const searchValue = normalizeText(subscriberSearch.value);
        const selectedPlan = planFilter.value;
        const selectedStatus = statusFilter.value;
        const selectedExpiry = expiryFilter.value;
        let visibleCount = 0;

        subscriberRows.forEach(function (row) {
            const matchesSearch =
                searchValue === "" ||
                normalizeText(row.textContent).includes(searchValue);
            const matchesPlan =
                selectedPlan === "all" || row.dataset.plan === selectedPlan;
            const matchesStatus =
                selectedStatus === "all" || row.dataset.status === selectedStatus;
            const matchesExpiry =
                selectedExpiry === "" || row.dataset.expiry <= selectedExpiry;
            const shouldDisplay =
                matchesSearch && matchesPlan && matchesStatus && matchesExpiry;

            row.hidden = !shouldDisplay;

            if (shouldDisplay) {
                visibleCount += 1;
            }
        });

        emptyResultRow.hidden = visibleCount !== 0;
        resultCount.innerHTML =
            "Showing <strong>" +
            visibleCount +
            "</strong> of <strong>" +
            subscriberRows.length +
            "</strong> loaded subscribers";
    }

    function resetFilters() {
        subscriberSearch.value = "";
        planFilter.value = "all";
        statusFilter.value = "all";
        expiryFilter.value = "";

        if (globalSearch) {
            globalSearch.value = "";
        }

        applyFilters();
        subscriberSearch.focus();
    }

    function escapeCsvValue(value) {
        const cleanValue = String(value || "").replace(/\s+/g, " ").trim();
        return '"' + cleanValue.replace(/"/g, '""') + '"';
    }

    function exportVisibleSubscribers() {
        const visibleRows = subscriberRows.filter(function (row) {
            return !row.hidden;
        });

        if (visibleRows.length === 0) {
            showMessage("No visible subscriber data is available to export.", "error");
            return;
        }

        const csvRows = [];
        const headings = Array.from(subscriberTable.querySelectorAll("thead th"));

        csvRows.push(
            headings
                .slice(0, -1)
                .map(function (heading) {
                    return escapeCsvValue(heading.textContent);
                })
                .join(",")
        );

        visibleRows.forEach(function (row) {
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
            "agent-subscriber-list-" + new Date().toISOString().slice(0, 10) + ".csv";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        downloadLink.remove();
        URL.revokeObjectURL(downloadUrl);

        showMessage(
            visibleRows.length + " subscriber records exported successfully.",
            "success"
        );
    }

    function getSelectedPlanPrice() {
        const selectedOption = planSelect.options[planSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            return "";
        }

        return billingSelect.value === "Yearly"
            ? selectedOption.dataset.yearly
            : selectedOption.dataset.monthly;
    }

    function updateSubscriptionPrice() {
        amountInput.value = getSelectedPlanPrice();
    }

    function addMonthsSafely(date, monthsToAdd) {
        const newDate = new Date(date.getTime());
        const originalDay = newDate.getDate();

        newDate.setDate(1);
        newDate.setMonth(newDate.getMonth() + monthsToAdd);

        const lastDayOfTargetMonth = new Date(
            newDate.getFullYear(),
            newDate.getMonth() + 1,
            0
        ).getDate();

        newDate.setDate(Math.min(originalDay, lastDayOfTargetMonth));
        return newDate;
    }

    function toDateInputValue(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return year + "-" + month + "-" + day;
    }

    function updateExpiryDate() {
        if (!startDateInput.value) {
            endDateInput.value = "";
            return;
        }

        const dateParts = startDateInput.value.split("-");
        const startDate = new Date(
            Number(dateParts[0]),
            Number(dateParts[1]) - 1,
            Number(dateParts[2])
        );
        const monthsToAdd = billingSelect.value === "Yearly" ? 12 : 1;
        const calculatedExpiry = addMonthsSafely(startDate, monthsToAdd);

        calculatedExpiry.setDate(calculatedExpiry.getDate() - 1);
        endDateInput.value = toDateInputValue(calculatedExpiry);
        endDateInput.min = startDateInput.value;
    }

    function calculateDaysLeft(expiryDateValue) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const expiryParts = expiryDateValue.split("-");
        const expiryDate = new Date(
            Number(expiryParts[0]),
            Number(expiryParts[1]) - 1,
            Number(expiryParts[2])
        );

        return Math.max(
            0,
            Math.ceil((expiryDate.getTime() - today.getTime()) / 86400000)
        );
    }

    function createSubscriptionRow(formData) {
        const agentSelect = subscriptionForm.querySelector('[name="agent_id"]');
        const selectedAgentText = agentSelect.options[agentSelect.selectedIndex].text;
        const agentParts = selectedAgentText.split(" Â· ");
        const agentId = agentParts[0] || "AGT-NEW";
        const company = agentParts[1] || "New Agent";
        const plan = formData.get("plan") || "Starter";
        const billing = formData.get("billing") || "Monthly";
        const amount = Number(formData.get("amount") || 0);
        const startDate = formData.get("start_date") || "";
        const expiryDate = formData.get("expiry_date") || "";
        const paymentStatus = formData.get("payment_status") || "Pending";
        const autoRenew = formData.get("auto_renew") === "1";
        const daysLeft = calculateDaysLeft(expiryDate);
        const subscriptionStatus = paymentStatus === "Paid" ? "Active" : "Pending";
        const subscriptionId = "SUB-" + Date.now().toString().slice(-5);
        const initials = company.substring(0, 2).toUpperCase();
        const planClass = normalizeText(plan);
        const statusClass = normalizeText(subscriptionStatus);
        const paymentClass = normalizeText(paymentStatus);

        const newRow = document.createElement("tr");
        newRow.dataset.plan = planClass;
        newRow.dataset.status = statusClass;
        newRow.dataset.expiry = expiryDate;
        newRow.dataset.amount = amount.toFixed(2);
        newRow.innerHTML =
            "<td><strong>" + escapeHtml(subscriptionId) + "</strong><small>" + escapeHtml(billing) + " subscription</small></td>" +
            '<td><div class="subscriber-agent"><span>' + escapeHtml(initials) + "</span><div><strong>" + escapeHtml(company) + "</strong><small>" + escapeHtml(agentId) + " Â· Email from agent record</small></div></div></td>" +
            '<td><span class="plan-badge ' + escapeHtml(planClass) + '"><i class="bi bi-gem"></i>' + escapeHtml(plan) + "</span><small>" + escapeHtml(billing) + " billing</small></td>" +
            "<td><strong>" + formatDisplayDate(startDate) + "</strong><small>to " + formatDisplayDate(expiryDate) + "</small></td>" +
            "<td><strong>" + formatDisplayDate(expiryDate) + '</strong><span class="days-left">' + daysLeft + " days left</span></td>" +
            '<td><strong class="subscription-amount">' + formatCurrency(amount) + "</strong><small>USD</small></td>" +
            '<td><span class="payment-badge ' + escapeHtml(paymentClass) + '"><i></i>' + escapeHtml(paymentStatus) + "</span></td>" +
            '<td><span class="subscription-status ' + escapeHtml(statusClass) + '"><i></i>' + escapeHtml(subscriptionStatus) + "</span></td>" +
            '<td><label class="renew-toggle" title="Auto renew"><input type="checkbox" ' + (autoRenew ? "checked" : "") + "><span></span></label></td>" +
            '<td><div class="subscriber-actions"><a href="#" title="View"><i class="bi bi-eye"></i></a><button type="button" title="Renew"><i class="bi bi-arrow-repeat"></i></button><button type="button" title="More"><i class="bi bi-three-dots"></i></button></div></td>';

        tableBody.insertBefore(newRow, tableBody.firstChild);
        subscriberRows.unshift(newRow);
    }

    function setDefaultStartDate() {
        const today = new Date();
        startDateInput.value = toDateInputValue(today);
        updateExpiryDate();
    }

    createEmptyResultRow();
    setDefaultStartDate();

    subscriberSearch.addEventListener("input", applyFilters);
    planFilter.addEventListener("change", applyFilters);
    statusFilter.addEventListener("change", applyFilters);
    expiryFilter.addEventListener("change", applyFilters);
    resetFilterButton.addEventListener("click", resetFilters);
    exportButton.addEventListener("click", exportVisibleSubscribers);

    if (globalSearch) {
        globalSearch.addEventListener("input", function () {
            subscriberSearch.value = globalSearch.value;
            applyFilters();
        });
    }

    planSelect.addEventListener("change", updateSubscriptionPrice);
    billingSelect.addEventListener("change", function () {
        updateSubscriptionPrice();
        updateExpiryDate();
    });
    startDateInput.addEventListener("change", updateExpiryDate);

    endDateInput.addEventListener("change", function () {
        endDateInput.setCustomValidity("");

        if (startDateInput.value && endDateInput.value < startDateInput.value) {
            endDateInput.setCustomValidity(
                "Expiry Date must be later than the Start Date."
            );
            endDateInput.reportValidity();
        }
    });

    document.querySelectorAll(".renew-toggle input").forEach(function (toggle) {
        toggle.addEventListener("change", function () {
            showMessage(
                "Auto-renew has been " +
                    (toggle.checked ? "enabled" : "disabled") +
                    ". Connect this toggle with your update query.",
                "success"
            );
        });
    });

    reminderButton.addEventListener("click", function () {
        const expiringCount = subscriberRows.filter(function (row) {
            return row.dataset.status === "expiring";
        }).length;

        showMessage(
            expiringCount +
                " renewal reminder email" +
                (expiringCount === 1 ? " is" : "s are") +
                " ready. Connect this button with your email API.",
            "success"
        );
    });

    subscriptionForm.addEventListener("submit", function (event) {
        event.preventDefault();
        endDateInput.setCustomValidity("");

        if (endDateInput.value < startDateInput.value) {
            endDateInput.setCustomValidity(
                "Expiry Date must be later than the Start Date."
            );
        }

        if (!subscriptionForm.checkValidity()) {
            subscriptionForm.classList.add("was-validated");
            subscriptionForm.reportValidity();
            return;
        }

        createSubscriptionRow(new FormData(subscriptionForm));

        const modalInstance = bootstrap.Modal.getInstance(subscriptionModal);

        if (modalInstance) {
            modalInstance.hide();
        }

        subscriptionForm.reset();
        subscriptionForm.classList.remove("was-validated");
        setDefaultStartDate();
        resetFilters();

        showMessage(
            "Subscription added to the table. Connect this form with your PHP insert query to save it permanently.",
            "success"
        );
    });

    subscriptionModal.addEventListener("hidden.bs.modal", function () {
        subscriptionForm.classList.remove("was-validated");
        endDateInput.setCustomValidity("");
    });

    document.addEventListener("keydown", function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
            event.preventDefault();
            subscriberSearch.focus();
            subscriberSearch.select();
        }
    });

    applyFilters();
});