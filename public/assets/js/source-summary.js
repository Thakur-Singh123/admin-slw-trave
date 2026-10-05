/* =========================================================
   SLW SUPER ADMIN - SOURCE SUMMARY
   Load after js/common.js
========================================================= */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var table = document.getElementById('sourceTable');
    if (!table) return;

    var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr'));
    var searchInput = document.getElementById('sourceSearch');
    var globalSearch = document.getElementById('globalSearch');
    var fromDate = document.getElementById('sourceFromDate');
    var toDate = document.getElementById('sourceToDate');
    var sourceType = document.getElementById('sourceType');
    var applyButton = document.getElementById('applySourceFilter');
    var resetButton = document.getElementById('resetSourceFilter');
    var exportButton = document.getElementById('exportSources');
    var pageLength = document.getElementById('sourcePageLength');
    var resultCount = document.getElementById('sourceResultCount');
    var visibleSourceCount = document.getElementById('visibleSourceCount');
    var visibleSourceBookings = document.getElementById('visibleSourceBookings');
    var visibleSourceAmount = document.getElementById('visibleSourceAmount');

    var filtersApplied = false;

    function normalise(value) {
        return String(value || '').toLowerCase().trim();
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('en-US').format(value);
    }

    function formatMoney(value) {
        return 'THB ' + new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }

    function getSearchTerm() {
        var localValue = searchInput ? searchInput.value : '';
        return normalise(localValue);
    }

    function datesAreValid() {
        if (fromDate.value && toDate.value && fromDate.value > toDate.value) {
            alert('From Date cannot be greater than To Date.');
            return false;
        }
        return true;
    }

    function rowMatches(row) {
        var searchTerm = getSearchTerm();
        var rowText = normalise(row.textContent);
        var rowType = normalise(row.getAttribute('data-source-type'));
        var rowDate = row.getAttribute('data-date') || '';
        var selectedType = sourceType ? normalise(sourceType.value) : 'all';

        var matchesSearch = !searchTerm || rowText.indexOf(searchTerm) !== -1;
        var matchesType = !filtersApplied || selectedType === 'all' || rowType === selectedType;
        var matchesFrom = !filtersApplied || !fromDate.value || rowDate >= fromDate.value;
        var matchesTo = !filtersApplied || !toDate.value || rowDate <= toDate.value;

        return matchesSearch && matchesType && matchesFrom && matchesTo;
    }

    function updateTotals(visibleRows) {
        var totalBookings = 0;
        var totalAmount = 0;

        visibleRows.forEach(function (row) {
            totalBookings += parseInt(row.getAttribute('data-bookings'), 10) || 0;
            totalAmount += parseFloat(row.getAttribute('data-amount')) || 0;
        });

        visibleSourceCount.textContent = visibleRows.length + (visibleRows.length === 1 ? ' source' : ' sources');
        visibleSourceBookings.textContent = formatNumber(totalBookings);
        visibleSourceAmount.textContent = formatMoney(totalAmount);
    }

    function updateResultText(totalMatched, shownCount) {
        if (!resultCount) return;

        if (totalMatched === 0) {
            resultCount.innerHTML = 'Showing <strong>0</strong> of <strong>0</strong> sources';
            return;
        }

        resultCount.innerHTML = 'Showing <strong>1â€“' + shownCount + '</strong> of <strong>' + totalMatched + '</strong> sources';
    }

    function addOrRemoveEmptyRow(totalMatched) {
        var oldEmptyRow = table.querySelector('.source-empty-row');
        if (oldEmptyRow) oldEmptyRow.remove();

        if (totalMatched === 0) {
            var emptyRow = document.createElement('tr');
            emptyRow.className = 'source-empty-row';
            emptyRow.innerHTML = '<td colspan="9" class="text-center py-5">' +
                '<i class="bi bi-search d-block mb-2 fs-4 text-secondary"></i>' +
                '<strong>No source record found</strong>' +
                '<small class="d-block mt-1 text-secondary">Change your search or filter and try again.</small>' +
                '</td>';
            table.querySelector('tbody').appendChild(emptyRow);
        }
    }

    function filterRows() {
        var limit = parseInt(pageLength.value, 10) || rows.length;
        var matchedRows = rows.filter(rowMatches);

        rows.forEach(function (row) {
            row.hidden = true;
        });

        matchedRows.slice(0, limit).forEach(function (row) {
            row.hidden = false;
        });

        updateTotals(matchedRows);
        updateResultText(matchedRows.length, Math.min(matchedRows.length, limit));
        addOrRemoveEmptyRow(matchedRows.length);
    }

    function resetFilters() {
        if (searchInput) searchInput.value = '';
        if (globalSearch) globalSearch.value = '';
        if (fromDate) fromDate.value = '2026-09-01';
        if (toDate) toDate.value = '2026-09-22';
        if (sourceType) sourceType.value = 'all';
        if (pageLength) pageLength.value = '10';
        filtersApplied = false;
        filterRows();
    }

    function escapeCsv(value) {
        var text = String(value || '').replace(/\s+/g, ' ').trim();
        return '"' + text.replace(/"/g, '""') + '"';
    }

    function exportCsv() {
        var visibleRows = rows.filter(function (row) {
            return !row.hidden;
        });

        if (!visibleRows.length) {
            alert('No source record is available to export.');
            return;
        }

        var csvRows = [[
            'Source Name', 'Source Type', 'Total Bookings', 'Confirmed',
            'Cancelled', 'Total Amount (THB)', 'Last Booking'
        ]];

        visibleRows.forEach(function (row) {
            var cells = row.querySelectorAll('td');
            var sourceName = cells[0].querySelector('strong');
            var sourceTypeText = cells[1].textContent;
            var confirmed = cells[4].textContent;
            var cancelled = cells[5].textContent;
            var lastBooking = cells[7].textContent;

            csvRows.push([
                sourceName ? sourceName.textContent : cells[0].textContent,
                sourceTypeText,
                row.getAttribute('data-bookings'),
                confirmed,
                cancelled,
                row.getAttribute('data-amount'),
                lastBooking
            ]);
        });

        var csvContent = csvRows.map(function (row) {
            return row.map(escapeCsv).join(',');
        }).join('\n');

        var blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
        var downloadUrl = URL.createObjectURL(blob);
        var downloadLink = document.createElement('a');
        downloadLink.href = downloadUrl;
        downloadLink.download = 'source-summary-' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        downloadLink.remove();
        URL.revokeObjectURL(downloadUrl);
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }

    if (globalSearch) {
        globalSearch.addEventListener('input', function () {
            searchInput.value = globalSearch.value;
            filterRows();
        });
    }

    if (applyButton) {
        applyButton.addEventListener('click', function () {
            if (!datesAreValid()) return;
            filtersApplied = true;
            filterRows();
        });
    }

    if (resetButton) resetButton.addEventListener('click', resetFilters);
    if (pageLength) pageLength.addEventListener('change', filterRows);
    if (exportButton) exportButton.addEventListener('click', exportCsv);

    document.addEventListener('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            searchInput.focus();
        }
    });

    filterRows();
});