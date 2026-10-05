/* =========================================================
   SLW SUPER ADMIN - AGENT TOP-UP REPORT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const topupTable = document.getElementById("topupReportTable");
    const tableBody = topupTable ? topupTable.querySelector("tbody") : null;
    const topupRows = tableBody
        ? Array.from(tableBody.querySelectorAll("tr"))
        : [];

    const globalSearch = document.getElementById("globalSearch");
    const topupSearch = document.getElementById("topupSearch");
    const fromDate = document.getElementById("topupFromDate");
    const toDate = document.getElementById("topupToDate");
    const statusFilter = document.getElementById("topupStatusFilter");
    const resetButton = document.getElementById("resetTopupFilter");
    const exportButton = document.getElementById("exportTopupReport");
    const tableResult = document.getElementById("topupTableResult");

    const currentMonthAmount = document.getElementById("currentMonthAmount");
    const uniqueAgentCount = document.getElementById("uniqueAgentCount");
    const transactionCount = document.getElementById("topupTransactionCount");
    const pendingAmount = document.getElementById("pendingTopupAmount");
    const visibleTopupTotal = document.getElementById("visibleTopupTotal");
    const topupTotalNote = document.getElementById("topupTotalNote");
    const chartYear = document.getElementById("chartYear");

    let monthlyChart = null;
    let paymentChart = null;
    let emptyResultRow = null;

    const chartDataByYear = {
        2026: {
            amounts: [5200, 6100, 7450, 6900, 8200, 9100, 8600, 10350, 8750, 0, 0, 0],
            agents: [8, 10, 12, 11, 14, 16, 15, 19, 6, 0, 0, 0]
        },
        2025: {
            amounts: [3800, 4200, 5100, 4750, 5900, 6400, 6100, 7200, 6850, 7600, 8100, 8900],
            agents: [6, 7, 8, 8, 10, 11, 10, 13, 12, 14, 15, 17]
        }
    };

    function normalizeText(value) {
        return String(value || "").trim().toLowerCase();
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: "USD",
            minimumFractionDigits: 2
        }).format(amount);
    }

    function showMessage(message, type) {
        const previousMessage = document.querySelector(".topup-report-alert");

        if (previousMessage) {
            previousMessage.remove();
        }

        const alertBox = document.createElement("div");
        const success = type === "success";

        alertBox.className =
            "alert alert-" +
            (success ? "success" : "danger") +
            " topup-report-alert d-flex align-items-center gap-2";
        alertBox.setAttribute("role", "alert");
        alertBox.innerHTML =
            '<i class="bi bi-' +
            (success ? "check-circle" : "exclamation-circle") +
            '"></i><span>' +
            message +
            "</span>";

        const pageHeading = document.querySelector(".topup-report-heading");

        if (pageHeading) {
            pageHeading.insertAdjacentElement("afterend", alertBox);
        }

        window.setTimeout(function () {
            if (alertBox.isConnected) {
                alertBox.remove();
            }
        }, 4000);
    }

    function createEmptyResultRow() {
        if (!tableBody || emptyResultRow) {
            return;
        }

        emptyResultRow = document.createElement("tr");
        emptyResultRow.className = "topup-empty-row";
        emptyResultRow.innerHTML =
            '<td colspan="8" class="text-center py-5">' +
            '<i class="bi bi-search d-block fs-3 text-secondary mb-2"></i>' +
            '<strong class="d-block">No top-up transaction found</strong>' +
            '<small class="d-block mt-1">Change your search, status or date range.</small>' +
            "</td>";
        emptyResultRow.hidden = true;
        tableBody.appendChild(emptyResultRow);
    }

    function getVisibleRows() {
        return topupRows.filter(function (row) {
            return !row.hidden;
        });
    }

    function updateSummaryCards(visibleRows) {
        let completedTotal = 0;
        let pendingTotal = 0;
        const uniqueAgents = new Set();

        visibleRows.forEach(function (row) {
            const amount = Number.parseFloat(row.dataset.amount || "0");
            const status = row.dataset.status || "";
            const agentId = row.dataset.agent || "";

            if (status === "completed") {
                completedTotal += amount;
            }

            if (status === "pending") {
                pendingTotal += amount;
            }

            if (agentId) {
                uniqueAgents.add(agentId);
            }
        });

        currentMonthAmount.textContent = formatCurrency(completedTotal);
        pendingAmount.textContent = formatCurrency(pendingTotal);
        uniqueAgentCount.textContent = uniqueAgents.size;
        transactionCount.textContent = visibleRows.length;
    }

    function updateTableTotal(visibleRows) {
        const totalAmount = visibleRows.reduce(function (sum, row) {
            return sum + Number.parseFloat(row.dataset.amount || "0");
        }, 0);

        visibleTopupTotal.textContent = formatCurrency(totalAmount);
        topupTotalNote.textContent =
            visibleRows.length +
            (visibleRows.length === 1
                ? " visible transaction"
                : " visible transactions");
    }

    function updatePaymentChart(visibleRows) {
        if (!paymentChart) {
            return;
        }

        const paymentTotals = {
            "Bank Transfer": 0,
            "Credit Card": 0,
            PayPal: 0,
            Cash: 0
        };

        visibleRows.forEach(function (row) {
            const paymentCell = row.cells[3];
            const paymentMethod = paymentCell
                ? paymentCell.textContent.trim()
                : "";
            const amount = Number.parseFloat(row.dataset.amount || "0");

            if (Object.prototype.hasOwnProperty.call(paymentTotals, paymentMethod)) {
                paymentTotals[paymentMethod] += amount;
            }
        });

        paymentChart.data.datasets[0].data = [
            paymentTotals["Bank Transfer"],
            paymentTotals["Credit Card"],
            paymentTotals.PayPal,
            paymentTotals.Cash
        ];
        paymentChart.update();

        const grandTotal = Object.values(paymentTotals).reduce(function (sum, value) {
            return sum + value;
        }, 0);

        const breakdownItems = document.querySelectorAll(".payment-breakdown strong");
        const values = Object.values(paymentTotals);

        breakdownItems.forEach(function (item, index) {
            const percentage = grandTotal > 0
                ? Math.round((values[index] / grandTotal) * 100)
                : 0;
            item.textContent = percentage + "%";
        });
    }

    function applyFilters() {
        const searchValue = normalizeText(topupSearch.value);
        const selectedStatus = statusFilter.value;
        const selectedFromDate = fromDate.value;
        const selectedToDate = toDate.value;

        topupRows.forEach(function (row) {
            const rowText = normalizeText(row.textContent);
            const rowDate = row.dataset.date || "";
            const rowStatus = row.dataset.status || "";

            const matchesSearch =
                searchValue === "" || rowText.includes(searchValue);
            const matchesStatus =
                selectedStatus === "all" || rowStatus === selectedStatus;
            const matchesFromDate =
                selectedFromDate === "" || rowDate >= selectedFromDate;
            const matchesToDate =
                selectedToDate === "" || rowDate <= selectedToDate;

            row.hidden = !(
                matchesSearch &&
                matchesStatus &&
                matchesFromDate &&
                matchesToDate
            );
        });

        const visibleRows = getVisibleRows();

        emptyResultRow.hidden = visibleRows.length !== 0;
        tableResult.innerHTML =
            "Showing <strong>" +
            visibleRows.length +
            "</strong> of <strong>" +
            topupRows.length +
            "</strong> transactions";

        updateSummaryCards(visibleRows);
        updateTableTotal(visibleRows);
        updatePaymentChart(visibleRows);
    }

    function validateDateRange() {
        if (fromDate.value && toDate.value && fromDate.value > toDate.value) {
            showMessage(
                "To Date must be the same as or later than From Date.",
                "error"
            );
            return false;
        }

        return true;
    }

    function handleDateChange(event) {
        if (!validateDateRange()) {
            event.target.value = "";
        }

        applyFilters();
    }

    function resetFilters() {
        topupSearch.value = "";
        fromDate.value = "2026-09-01";
        toDate.value = "2026-09-30";
        statusFilter.value = "all";

        if (globalSearch) {
            globalSearch.value = "";
        }

        applyFilters();
        topupSearch.focus();
    }

    function escapeCsvValue(value) {
        const cleanValue = String(value || "").replace(/\s+/g, " ").trim();
        return '"' + cleanValue.replace(/"/g, '""') + '"';
    }

    function exportVisibleRows() {
        const visibleRows = getVisibleRows();

        if (visibleRows.length === 0) {
            showMessage("No visible transactions are available to export.", "error");
            return;
        }

        const csvLines = [];
        const headings = Array.from(topupTable.querySelectorAll("thead th"));

        csvLines.push(
            headings
                .slice(0, -1)
                .map(function (heading) {
                    return escapeCsvValue(heading.textContent);
                })
                .join(",")
        );

        visibleRows.forEach(function (row) {
            const cells = Array.from(row.querySelectorAll("td")).slice(0, -1);
            csvLines.push(
                cells
                    .map(function (cell) {
                        return escapeCsvValue(cell.textContent);
                    })
                    .join(",")
            );
        });

        const csvFile = new Blob(["\uFEFF" + csvLines.join("\n")], {
            type: "text/csv;charset=utf-8;"
        });
        const downloadUrl = URL.createObjectURL(csvFile);
        const downloadLink = document.createElement("a");

        downloadLink.href = downloadUrl;
        downloadLink.download =
            "agent-topup-report-" + new Date().toISOString().slice(0, 10) + ".csv";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        downloadLink.remove();
        URL.revokeObjectURL(downloadUrl);

        showMessage(
            visibleRows.length + " top-up transactions exported successfully.",
            "success"
        );
    }

    function createMonthlyChart() {
        const canvas = document.getElementById("monthlyTopupChart");

        if (!canvas || typeof Chart === "undefined") {
            return;
        }

        const selectedData = chartDataByYear[chartYear.value];

        monthlyChart = new Chart(canvas, {
            type: "bar",
            data: {
                labels: [
                    "Jan", "Feb", "Mar", "Apr", "May", "Jun",
                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                ],
                datasets: [
                    {
                        label: "Top-up Amount (USD)",
                        data: selectedData.amounts,
                        backgroundColor: "rgba(255, 83, 53, 0.82)",
                        borderColor: "#ff5335",
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 34,
                        yAxisID: "amountAxis"
                    },
                    {
                        type: "line",
                        label: "Unique Agents",
                        data: selectedData.agents,
                        borderColor: "#347bd7",
                        backgroundColor: "#347bd7",
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        yAxisID: "agentAxis"
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: "index",
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: "#111c32",
                        padding: 11,
                        titleFont: { size: 11 },
                        bodyFont: { size: 10 },
                        callbacks: {
                            label: function (context) {
                                if (context.dataset.yAxisID === "amountAxis") {
                                    return " Amount: " + formatCurrency(context.parsed.y);
                                }

                                return " Agents: " + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: "#8d97a7", font: { size: 9 } }
                    },
                    amountAxis: {
                        position: "left",
                        beginAtZero: true,
                        grid: { color: "rgba(221, 226, 234, 0.65)" },
                        ticks: {
                            color: "#8d97a7",
                            font: { size: 9 },
                            callback: function (value) {
                                return "$" + Number(value).toLocaleString("en-US");
                            }
                        }
                    },
                    agentAxis: {
                        position: "right",
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: {
                            color: "#8d97a7",
                            font: { size: 9 },
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    function createPaymentChart() {
        const canvas = document.getElementById("paymentMethodChart");

        if (!canvas || typeof Chart === "undefined") {
            return;
        }

        paymentChart = new Chart(canvas, {
            type: "doughnut",
            data: {
                labels: ["Bank Transfer", "Credit Card", "PayPal", "Cash"],
                datasets: [
                    {
                        data: [0, 0, 0, 0],
                        backgroundColor: ["#ff5335", "#347bd7", "#7654d4", "#00a65a"],
                        borderColor: "#ffffff",
                        borderWidth: 4,
                        hoverOffset: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "68%",
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: "#111c32",
                        padding: 10,
                        bodyFont: { size: 10 },
                        callbacks: {
                            label: function (context) {
                                return " " + context.label + ": " + formatCurrency(context.parsed);
                            }
                        }
                    }
                }
            }
        });
    }

    createEmptyResultRow();
    createMonthlyChart();
    createPaymentChart();

    topupSearch.addEventListener("input", applyFilters);
    statusFilter.addEventListener("change", applyFilters);
    fromDate.addEventListener("change", handleDateChange);
    toDate.addEventListener("change", handleDateChange);
    resetButton.addEventListener("click", resetFilters);
    exportButton.addEventListener("click", exportVisibleRows);

    if (globalSearch) {
        globalSearch.addEventListener("input", function () {
            topupSearch.value = globalSearch.value;
            applyFilters();
        });
    }

    chartYear.addEventListener("change", function () {
        const selectedData = chartDataByYear[chartYear.value];

        if (!monthlyChart || !selectedData) {
            return;
        }

        monthlyChart.data.datasets[0].data = selectedData.amounts;
        monthlyChart.data.datasets[1].data = selectedData.agents;
        monthlyChart.update();
    });

    document.addEventListener("keydown", function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
            event.preventDefault();
            topupSearch.focus();
            topupSearch.select();
        }
    });

    applyFilters();
});