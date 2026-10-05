/* SLW SUPER ADMIN - AGENT BOOKING SUMMARY */
$(function () {
    'use strict';

    var fromDate = $('#agentFromDate');
    var toDate = $('#agentToDate');
    var countryFilter = $('#agentCountryFilter');
    var statusFilter = $('#agentStatusFilter');

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'agentBookingTable') return true;

        var row = settings.aoData[dataIndex].nTr;
        var lastBooking = row.getAttribute('data-last-booking') || '';
        var selectedCountry = countryFilter.val();
        var selectedStatus = statusFilter.val();
        var rowCountry = $('<div>').html(data[1]).text().trim();
        var rowStatus = $('<div>').html(data[8]).text().trim();

        if (fromDate.val() && lastBooking < fromDate.val()) return false;
        if (toDate.val() && lastBooking > toDate.val()) return false;
        if (selectedCountry && rowCountry.indexOf(selectedCountry) === -1) return false;
        if (selectedStatus && rowStatus.indexOf(selectedStatus) === -1) return false;

        return true;
    });

    var table = new DataTable('#agentBookingTable', {
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[2, 'desc']],
        columnDefs: [
            { orderable: false, targets: [8] }
        ],
        language: {
            search: '',
            searchPlaceholder: 'Search agent, company, email or country...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_â€“_END_ of _TOTAL_ booking agents',
            infoEmpty: 'No booking agent found',
            zeroRecords: 'No matching booking agent found'
        }
    });

    table.on('draw', function () {
        updateFilteredTotals();
        updateSettlementLinks();
    });

    function money(currency, amount) {
        return currency + ' ' + new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function prettyDate(value) {
        if (!value) return '';
        return new Date(value + 'T00:00:00').toLocaleDateString('en-GB', {
            day: '2-digit', month: 'short', year: 'numeric'
        });
    }

    function updateFilteredTotals() {
        var rows = table.rows({ search: 'applied' }).nodes().toArray();
        var bookings = 0;
        var net = 0;
        var sell = 0;
        var wallet = 0;

        rows.forEach(function (row) {
            bookings += parseInt(row.getAttribute('data-bookings'), 10) || 0;
            net += parseFloat(row.getAttribute('data-net')) || 0;
            sell += parseFloat(row.getAttribute('data-sell')) || 0;
            wallet += parseFloat(row.getAttribute('data-wallet')) || 0;
        });

        $('#cardAgents').text(rows.length.toLocaleString('en-US'));
        $('#cardBookings, #footerBookings').text(bookings.toLocaleString('en-US'));
        $('#cardSales, #footerSales').text(money('THB', sell));
        $('#cardWallet, #footerWallet').text(money('USD', wallet));
        $('#footerNet').text(money('THB', net));
        $('#footerMargin').text(money('THB', sell - net));
    }

    function updateSettlementLinks() {
        var from = fromDate.val();
        var to = toDate.val();

        $('#agentBookingTable .booking-count-link').each(function () {
            var url = new URL(this.href, window.location.href);
            url.searchParams.set('from', from);
            url.searchParams.set('to', to);
            this.href = url.pathname + url.search;
        });
    }

    function applyFilters() {
        if (fromDate.val() && toDate.val() && fromDate.val() > toDate.val()) {
            alert('Booking From Date cannot be greater than Booking To Date.');
            return;
        }

        $('#activePeriod').text(prettyDate(fromDate.val()) + ' â€“ ' + prettyDate(toDate.val()));
        table.draw();
    }

    $('#applyAgentFilter').on('click', applyFilters);

    $('#resetAgentFilter').on('click', function () {
        fromDate.val('2026-09-01');
        toDate.val('2026-09-24');
        countryFilter.val('');
        statusFilter.val('');
        table.search('').columns().search('');
        applyFilters();
    });

    $('#globalSearch').on('input', function () {
        table.search(this.value).draw();
    });

    $(document).on('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            $('.dt-search input').trigger('focus');
        }
    });

    $('#exportAgentSummary').on('click', function () {
        var csvRows = [['Agent', 'Email', 'Country', 'Bookings', 'Net Cost THB', 'Gross Sales THB', 'Margin THB', 'Wallet USD', 'Last Booking', 'Status']];

        table.rows({ search: 'applied' }).nodes().toArray().forEach(function (row) {
            var cells = row.cells;
            var company = $(cells[0]).find('strong').first().text().trim();
            var email = $(cells[0]).find('a').text().trim();
            var net = parseFloat(row.getAttribute('data-net')) || 0;
            var sell = parseFloat(row.getAttribute('data-sell')) || 0;

            csvRows.push([
                company,
                email,
                $(cells[1]).text().trim(),
                row.getAttribute('data-bookings'),
                net.toFixed(2),
                sell.toFixed(2),
                (sell - net).toFixed(2),
                (parseFloat(row.getAttribute('data-wallet')) || 0).toFixed(2),
                row.getAttribute('data-last-booking'),
                $(cells[8]).text().trim()
            ]);
        });

        var escapeCsv = function (value) {
            return '"' + String(value).replace(/"/g, '""') + '"';
        };
        var csv = csvRows.map(function (row) {
            return row.map(escapeCsv).join(',');
        }).join('\n');
        var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var objectUrl = URL.createObjectURL(blob);
        link.href = objectUrl;
        link.download = 'agent-booking-summary-' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(objectUrl);
    });

    updateFilteredTotals();
    updateSettlementLinks();
});