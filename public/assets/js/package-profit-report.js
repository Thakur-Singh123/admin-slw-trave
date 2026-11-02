
$(function () {
    'use strict';
    $('#packageDetailModal, #addPaymentModal, #paymentHistoryModal').appendTo('body');
    var fromDate = $('#reportFromDate');
    var toDate = $('#reportToDate');
    var country = $('#reportCountry');
    var customerStatus = $('#customerPaymentFilter');
    var supplierStatus = $('#supplierPaymentFilter');
    var profitStatus = $('#profitFilter');
    var activePaymentRow = null;
    var filterTimer = null;
    var loaderStartTime = 0;
    var loaderTimer = null;
    // Package Loader
    function createPackageLoader() {
        if ($('#packageAjaxLoader').length) return;

        $('body').append(`
            <div id="packageAjaxLoader">
                <div class="package-loader-box">
                    <div class="package-loader-spinner"></div>
                    <div class="package-loader-text">
                        Loading packages...
                    </div>
                </div>
            </div>
        `);

        $('<style>')
            .attr('id', 'packageAjaxLoaderStyle')
            .html(`
                #packageAjaxLoader {
                    position: fixed;
                    inset: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,.35);
                    display: none;
                    align-items: center;
                    justify-content: center;
                    z-index: 999999;
                }

                .package-loader-box {
                    background: #fff;
                    padding: 22px 30px;
                    border-radius: 12px;
                    text-align: center;
                    box-shadow: 0 10px 35px rgba(0,0,0,.20);
                    min-width: 180px;
                }

                .package-loader-spinner {
                    width: 42px;
                    height: 42px;
                    margin: 0 auto 12px;
                    border: 4px solid #e9ecef;
                    border-top: 4px solid #ff5d43;
                    border-radius: 50%;
                    animation: packageLoaderSpin .8s linear infinite;
                }

                .package-loader-text {
                    font-size: 14px;
                    font-weight: 600;
                    color: #172033;
                }

                @keyframes packageLoaderSpin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `)
            .appendTo('head');
    }

    createPackageLoader();

    function showPackageLoader() {
        clearTimeout(loaderTimer);

        loaderStartTime = Date.now();

        $('#packageAjaxLoader')
            .stop(true, true)
            .css('display', 'flex');
    }

    function hidePackageLoader() {
        var elapsed = Date.now() - loaderStartTime;
        var remaining = Math.max(0, 2000 - elapsed);

        clearTimeout(loaderTimer);

        loaderTimer = setTimeout(function () {
            $('#packageAjaxLoader')
                .stop(true, true)
                .hide();
        }, remaining);
    }

    function applyPackageFilter() {
        if (
            fromDate.val() &&
            toDate.val() &&
            fromDate.val() > toDate.val()
        ) {
            hidePackageLoader();
            return;
        }

        clearTimeout(filterTimer);
        showPackageLoader();

        filterTimer = setTimeout(function () {
            table.draw();
        }, 30);
    }

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'packageProfitTable') return true;

        var row = settings.aoData[dataIndex].nTr;
        var rowDate = row.getAttribute('data-booking-date') || '';
        var rowProfit = parseFloat(row.getAttribute('data-profit')) || 0;

        if (fromDate.val() && rowDate < fromDate.val()) return false;
        if (toDate.val() && rowDate > toDate.val()) return false;

        if (
            country.val() &&
            row.getAttribute('data-country') !== country.val()
        ) return false;

        if (
            customerStatus.val() &&
            row.getAttribute('data-customer-status') !== customerStatus.val()
        ) return false;

        if (
            supplierStatus.val() &&
            row.getAttribute('data-supplier-status') !== supplierStatus.val()
        ) return false;

        if (profitStatus.val() === 'profit' && rowProfit < 0) return false;
        if (profitStatus.val() === 'loss' && rowProfit >= 0) return false;

        return true;
    });

    var table = new DataTable('#packageProfitTable', {
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [9] }],
        language: {
            search: '',
            searchPlaceholder: 'Search package, reference, country or customer...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_–_END_ of _TOTAL_ package bookings',
            infoEmpty: 'No package booking found',
            zeroRecords: 'No matching package booking found'
        }
    });

    function number(value) {
        var parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function money(value, currency) {
        return (currency || 'THB') + ' ' +
            new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(number(value));
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    // function updateTotals() {
    //     var rows = table.rows({ search: 'applied' }).nodes().toArray();
    //     var sale = 0, cost = 0, profit = 0, received = 0, due = 0;

    //     rows.forEach(function (row) {
    //         sale += number(row.getAttribute('data-sale'));
    //         cost += number(row.getAttribute('data-cost'));
    //         profit += number(row.getAttribute('data-profit'));
    //         received += number(row.getAttribute('data-received'));
    //         due += number(row.getAttribute('data-due'));
    //     });

    //     $('#statBookings').text(rows.length);
    //     $('#statSales, #footerSale').text(money(sale));
    //     $('#statProfit, #footerProfit').text(money(profit));
    //     $('#statDue, #footerDue').text(money(due));
    //     $('#footerCost').text(money(cost));
    //     $('#footerReceived').text(money(received));

    //     $('#statMargin').text(
    //         (sale > 0 ? (profit / sale) * 100 : 0).toFixed(2) +
    //         '% profit margin'
    //     );
    // }
    function updateTotals() {
    var rows = table.rows({ search: 'applied' }).nodes().toArray();
    var sale = 0, cost = 0, profit = 0, received = 0, due = 0, supplierPaid = 0, supplierDue = 0;

    rows.forEach(function (row) {
        sale += number(row.getAttribute('data-sale'));
        cost += number(row.getAttribute('data-cost'));
        profit += number(row.getAttribute('data-profit'));
        received += number(row.getAttribute('data-received'));
        due += number(row.getAttribute('data-due'));
        supplierPaid += number(row.getAttribute('data-supplier-paid'));
        supplierDue += number(row.getAttribute('data-supplier-due'));
    });

    $('#statBookings').text(rows.length);
    $('#statSales, #footerSale').text(money(sale));
    $('#statProfit, #footerProfit').text(money(profit));
    $('#statDue, #footerDue').text(money(due));
    $('#footerCost').text(money(cost));
    $('#footerReceived').text(money(received));
    $('#footerSupplierPaid').text(money(supplierPaid));
    $('#footerSupplierDue').text(money(supplierDue));

    $('#statMargin').text(
        (sale > 0 ? (profit / sale) * 100 : 0).toFixed(2) +
        '% profit margin'
    );
}

    table.on('draw', function () {
        updateTotals();
        hidePackageLoader();
    });

    // Automatic date and dropdown filtering
    fromDate.add(toDate)
        .add(country)
        .add(customerStatus)
        .add(supplierStatus)
        .add(profitStatus)
        .on('input change', function () {
            applyPackageFilter();
        });

    // Search while typing
    $('#globalSearch').off('input').on('input', function () {
        clearTimeout(filterTimer);
        showPackageLoader();

        var searchValue = this.value;

        filterTimer = setTimeout(function () {
            table.search(searchValue).draw();
        }, 200);
    });

    // Also support DataTable's built-in search input
    $('#packageProfitTable_wrapper .dt-search input, #packageProfitTable_wrapper .dataTables_filter input')
        .off('input.packageFilter')
        .on('input.packageFilter', function () {
            clearTimeout(filterTimer);
            showPackageLoader();

            var searchValue = this.value;

            filterTimer = setTimeout(function () {
                table.search(searchValue).draw();
            }, 200);
        });

    // Reset filters
    $('#resetPackageFilter').off('click').on('click', function () {
        clearTimeout(filterTimer);

        fromDate.val('');
        toDate.val('');
        country.val('');
        customerStatus.val('');
        supplierStatus.val('');
        profitStatus.val('');

        $('.country-summary-strip button').removeClass('active');
        $('#globalSearch').val('');

        $('#packageProfitTable_wrapper .dt-search input, #packageProfitTable_wrapper .dataTables_filter input')
            .val('');

        showPackageLoader();
        table.search('').draw();
    });

    // Country summary buttons
    $('.country-summary-strip button').off('click').on('click', function () {
        var selected = $(this).attr('data-country');
        var isActive = $(this).hasClass('active');

        $('.country-summary-strip button').removeClass('active');

        country.val(isActive ? '' : selected);

        if (!isActive) {
            $(this).addClass('active');
        }

        applyPackageFilter();
    });

    // View package details
    $(document).on('click', '.view-package', function () {
        var row = $(this).closest('tr');
        var currencyCode = row.attr('data-currency') || 'THB';

        $('#detailReference').text(row.attr('data-reference'));
        $('#detailPackageName').text(row.attr('data-package-name'));

        $('#detailCountryCustomer').text(
            row.attr('data-country') + ' · ' +
            row.find('td').eq(2).find('strong').text().trim()
        );

        $('#detailSale').text(money(row.attr('data-sale'), currencyCode));
        $('#detailCost').text(money(row.attr('data-cost'), currencyCode));
        $('#detailProfit').text(money(row.attr('data-profit'), currencyCode));
        $('#detailDue').text(money(row.attr('data-due'), currencyCode));

        // $('#editPackageLink').attr(
        //     'href',
        //     'package-payment-add.php?id=' + encodeURIComponent(row.attr('data-id'))
        // );
    });

    function paymentValues() {
        if (!activePaymentRow) {
            return { total: 0, paid: 0, due: 0, currency: 'THB' };
        }

        var supplier = $('#transactionType').val() === 'Supplier Payment';

        return {
            total: number(activePaymentRow.attr(
                supplier ? 'data-supplier-total' : 'data-sale'
            )),
            paid: number(activePaymentRow.attr(
                supplier ? 'data-supplier-paid' : 'data-received'
            )),
            due: number(activePaymentRow.attr(
                supplier ? 'data-supplier-due' : 'data-due'
            )),
            currency: activePaymentRow.attr('data-currency') || 'THB'
        };
    }

    function refreshPaymentSummary() {
        var values = paymentValues();
        var amount = number($('#transactionAmount').val());
        var done = values.due <= 0;

        $('#paymentTotalAmount').text(money(values.total, values.currency));
        $('#paymentAlreadyPaid').text(money(values.paid, values.currency));
        $('#paymentRemainingDue').text(money(values.due, values.currency));
        $('#paymentCurrencyLabel').text(values.currency);

        $('#transactionAmount').attr('max', values.due.toFixed(2));

        $('#amountValidationText').text(
            done ? 'No pending amount.' : 'Maximum allowed: ' + money(values.due, values.currency)
        );

        $('#balanceAfterPayment').text(
            money(Math.max(0, values.due - amount), values.currency)
        );

        $('#addPackagePaymentForm .payment-form-grid')
            .find('input, select, textarea')
            .not('#transactionType')
            .prop('disabled', done);
        $('#savePaymentButton').toggle(!done);
        $('#allPaymentDone').css('display', done ? 'inline-flex' : 'none');
    }

    function formatNumber(value) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number(value));
}

function statusSlug(status) {
    return String(status || '').toLowerCase().replace(/\s+/g, '-');
}

    function updatePaymentRow(row, res) {
        var c = res.customer;
        var s = res.supplier;

        row.attr('data-received', number(c.received).toFixed(2))
            .attr('data-due', number(c.due).toFixed(2))
            .attr('data-customer-status', c.status)
            .attr('data-supplier-paid', number(s.paid).toFixed(2))
            .attr('data-supplier-due', number(s.due).toFixed(2))
            .attr('data-supplier-status', s.status);

        row.find('td').eq(7).html(
            '<span class="status-pill ' + statusSlug(c.status) + '"><i></i>' + escapeHtml(c.status) + '</span>' +
            '<small>Received: ' + formatNumber(c.received) + '</small>' +
            '<small>Due: ' + formatNumber(c.due) + '</small>'
        );
        row.find('td').eq(8).html(
            '<span class="status-pill ' + statusSlug(s.status) + '"><i></i>' + escapeHtml(s.status) + '</span>' +
            '<small>Paid: ' + formatNumber(s.paid) + '</small>' +
            '<small>Due: ' + formatNumber(s.due) + '</small>'
        );

        var history = res.history || [];
        var historyButton = row.find('.view-payment-history');
        historyButton.attr('data-history', JSON.stringify(history));
        var badge = historyButton.find('span');
        if (history.length) {
            if (!badge.length) badge = $('<span>').appendTo(historyButton);
            badge.text(history.length);
        } else {
            badge.remove();
        }

        // DataTable ko batao row badli hai (page/filter wahi rahega)
        table.row(row).invalidate('dom').draw(false);
    }

    $(document).on('click', '.add-package-payment', function () {
        activePaymentRow = $(this).closest('tr');

        var form = document.getElementById('addPackagePaymentForm');
        if (form) form.reset();

        $('#paymentPackageId').val(activePaymentRow.attr('data-id'));
        $('#paymentPackageReference').text(activePaymentRow.attr('data-reference'));
        $('#paymentPackageName').text(activePaymentRow.attr('data-package-name'));

        $('#paymentPackageCountry').text(
            activePaymentRow.attr('data-country') + ' · ' +
            activePaymentRow.find('td').eq(2).find('strong').text().trim()
        );

        $('#transactionDate').val(new Date().toISOString().slice(0, 10));
        $('#transactionAmount').removeClass('is-invalid');

        refreshPaymentSummary();
    });

    $('#transactionType').on('change', function () {
        $('#transactionAmount').val('').removeClass('is-invalid');
        refreshPaymentSummary();
    });

    $('#transactionAmount').on('input', function () {
        var values = paymentValues();
        var amount = number(this.value);

        $(this).toggleClass(
            'is-invalid',
            amount <= 0 || amount > values.due
        );

        refreshPaymentSummary();
    });

    function safeReceiptLink(path) {
    var value = String(path || '').trim();

    if (!value) {
        return '<span class="no-receipt">—</span>';
    }

    value = value.replace(/\\/g, '/');

    // Full URL
    if (/^https?:\/\//i.test(value)) {
        return '<a class="receipt-link" href="' + escapeHtml(value) +
            '" target="_blank" rel="noopener">' +
            '<i class="bi bi-file-earmark-text"></i> View</a>';
    }

    // Remove leading slash
    value = value.replace(/^\/+/, '');

    // Add upload folder if only filename is stored
    if (!value.includes('/')) {
        value = 'uploads/package-payment/' + value;
    }

    var url = new URL(value, window.location.href).href;

    return '<a class="receipt-link" href="' + escapeHtml(url) +
        '" target="_blank" rel="noopener">' +
        '<i class="bi bi-file-earmark-text"></i> View</a>';
}

    function invoiceLink(url) {
        if (!url) return '<span class="no-receipt">—</span>';
        return '<a class="receipt-link" href="' + escapeHtml(url) +
            '" target="_blank" rel="noopener">' +
            '<i class="bi bi-receipt"></i> Invoice</a>';
    }

    $(document).on('click', '.view-payment-history', function () {
        var button = $(this);
        var row = button.closest('tr');
        var history = [];

        try {
            history = JSON.parse(button.attr('data-history') || '[]');
        } catch (error) {
            history = [];
        }

        $('#historyPackageReference').text(row.attr('data-reference'));
        $('#historyPackageName').text(row.attr('data-package-name'));

        $('#historyTransactionCount').text(
            history.length +
            (history.length === 1 ? ' transaction' : ' transactions')
        );

        $('#paymentHistoryBody').empty();
        $('#paymentHistoryEmpty').toggle(history.length === 0);
        $('.payment-history-table').toggle(history.length > 0);

        history.forEach(function (item) {
            var typeClass = item.transaction_type === 'Supplier Payment'
                ? 'supplier'
                : 'customer';

            var date = item.payment_date ||
                String(item.created_at || '').slice(0, 10) || '—';

            $('#paymentHistoryBody').append(
                '<tr>' +
                '<td><strong>' + escapeHtml(date) + '</strong></td>' +
                '<td><span class="transaction-type ' + typeClass + '">' +
                escapeHtml(item.transaction_type) + '</span></td>' +
                '<td><strong class="history-amount">' +
                escapeHtml(money(item.amount, item.currency)) + '</strong></td>' +
                '<td>' + escapeHtml(item.payment_mode || '—') + '</td>' +
                '<td>' + escapeHtml(item.transaction_reference || '—') + '</td>' +
                '<td class="history-remark">' +
                escapeHtml(item.remark || '—') + '</td>' +
                '<td>' + safeReceiptLink(item.receipt_path) + '</td>' +
                '<td>' + invoiceLink(item.invoice_url) + '</td>' +
                '</tr>'
            );
        });
    });

    // Export CSV
    $('#exportPackageReport').off('click').on('click', function (e) {
        e.preventDefault();

        var params = new URLSearchParams();

        params.set('from_date', fromDate.val() || '');
        params.set('to_date', toDate.val() || '');
        params.set('country', country.val() || '');
        params.set('customer_status', customerStatus.val() || '');
        params.set('supplier_status', supplierStatus.val() || '');
        params.set('profit_status', profitStatus.val() || '');

        var searchValue = $('#globalSearch').val();

        if (searchValue === undefined || searchValue === '') {
            searchValue = $(
                '#packageProfitTable_wrapper .dt-search input, ' +
                '#packageProfitTable_wrapper .dataTables_filter input'
            ).first().val() || '';
        }

        params.set('search', searchValue);

        var exportUrl = $(this).attr('data-export-url');

        if (!exportUrl) {
            alert('Export URL missing. Check the Blade button.');
            return;
        }

        window.location.href = exportUrl + '?' + params.toString();
    });

    updateTotals();
});

//Delete package delete
$(document).on('submit', '.delete-package-form', function (e) {
    e.preventDefault();

    let form = this;

    Swal.fire({
        title: 'Are you sure?',
        text: 'This package payment will be deleted permanently!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff5635',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
