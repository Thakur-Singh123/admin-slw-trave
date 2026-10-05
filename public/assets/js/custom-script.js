$(document).ready(function () {
    let selectedType = $('#bookingTypeTabs button.active').data('type') || 'all';
    let bookingRequest = null;
    let bookingTable = null;
    let searchTimer = null;

    function createBookingLoader() {
        if ($('#bookingAjaxLoader').length) {
            return;
        }
        $('body').append(`
            <div id="bookingAjaxLoader">
                <div class="booking-loader-box">
                    <div class="booking-loader-spinner"></div>
                    <div class="booking-loader-text">
                        Loading bookings...
                    </div>
                </div>
            </div>
        `);
        $('<style>')
            .attr('id', 'bookingAjaxLoaderStyle')
            .html(`
                #bookingAjaxLoader{
                    position:fixed;
                    inset:0;
                    width:100%;
                    height:100%;
                    background:rgba(0,0,0,.35);
                    display:none;
                    align-items:center;
                    justify-content:center;
                    z-index:999999;
                }
                .booking-loader-box{
                    background:#fff;
                    padding:22px 30px;
                    border-radius:12px;
                    text-align:center;
                    box-shadow:0 10px 35px rgba(0,0,0,.20);
                    min-width:180px;
                }
                .booking-loader-spinner{
                    width:42px;
                    height:42px;
                    margin:0 auto 12px;
                    border:4px solid #e9ecef;
                    border-top:4px solid #ff5d43;
                    border-radius:50%;
                    animation:bookingLoaderSpin .8s linear infinite;
                }
                .booking-loader-text{
                    font-size:14px;
                    font-weight:600;
                    color:#172033;
                }
                @keyframes bookingLoaderSpin{
                    0%{transform:rotate(0deg)}
                    100%{transform:rotate(360deg)}
                }
            `)
            .appendTo('head');
    }
    createBookingLoader();
    function showBookingLoader() {
        $('#bookingAjaxLoader').css('display', 'flex');
    }
    function hideBookingLoader() {
        $('#bookingAjaxLoader').fadeOut(150);
    }
    function initBookingDataTable() {
        if (!$('#bookingTable').length) {
            return;
        }
        if ($.fn.DataTable.isDataTable('#bookingTable')) {
            $('#bookingTable').DataTable().destroy();
            bookingTable = null;
        }
        let footer = $('#bookingTable tfoot').detach();
        $('#bookingTable tbody tr').each(function () {
            let cells = $(this).find('td,th');
            if (
                cells.length === 1 &&
                cells.first().attr('colspan')
            ) {
                $(this).remove();
            }
        });
        bookingTable = $('#bookingTable').DataTable({
            paging: false,
            searching: true,
            ordering: true,
            info: false,
            lengthChange: false,
            autoWidth: false,
            scrollX: true,
            dom: 'rt',
            language: {
                emptyTable: 'No bookings found.',
                zeroRecords: 'No bookings found.'
            }
        });
        if (footer.length) {
            $('#bookingTable').append(footer);
        }
        $('#bookingSearch')
            .off('keyup.bookingSearch')
            .on('keyup.bookingSearch', function () {
                let value = this.value;
                clearTimeout(searchTimer);
                showBookingLoader();
                searchTimer = setTimeout(function () {
                    if (bookingTable) {
                        bookingTable
                            .search(value)
                            .draw();
                    }
                    hideBookingLoader();
                }, 150);
            });
    }
    function loadBookings(page = 1) {
        if (
            bookingRequest &&
            bookingRequest.readyState !== 4
        ) {
            bookingRequest.abort();
        }
        showBookingLoader();
        let requestStartTime = Date.now();
        let currentRequest = $.ajax({
            url: base_url + '/admin/bookings',
            type: 'GET',
            data: {
                page: page,
                type: selectedType,
                booking_from:
                    $('#bookingFromDate').val(),
                booking_to:
                    $('#bookingToDate').val(),
                travel_from:
                    $('#travelFromDate').val(),
                travel_to:
                    $('#travelToDate').val(),
                status:
                    $('#bookingStatusFilter').val() || 'all',
                source:
                    $('#bookingSourceFilter').val() || 'all'
            },
            success: function (response) {
                let html = $('<div>').html(response);
                if (
                    $.fn.DataTable.isDataTable('#bookingTable')
                ) {
                    $('#bookingTable')
                        .DataTable()
                        .destroy();

                    bookingTable = null;
                }
                $('#bookingTable tbody').html(
                    html.find(
                        '#bookingTable tbody'
                    ).html()
                );
                $('#bookingTable tfoot').html(
                    html.find(
                        '#bookingTable tfoot'
                    ).html()
                );
                $('.booking-footer').html(
                    html.find(
                        '.booking-footer'
                    ).html()
                );
                $('#bookingTypeTabs').html(
                    html.find(
                        '#bookingTypeTabs'
                    ).html()
                );
                $('#bookingTypeTabs button')
                    .removeClass('active')
                    .filter(
                        '[data-type="' +
                        selectedType +
                        '"]'
                    )
                    .addClass('active');
                updateFilterCount();
                initBookingDataTable();
            },
            error: function (xhr, status) {
                if (status !== 'abort') {
                    console.log(xhr.responseText);
                }

            },
            complete: function () {
                if (bookingRequest !== currentRequest) {
                    return;
                }
                let elapsed =
                    Date.now() - requestStartTime;
                let remaining =
                    Math.max(0, 300 - elapsed);
                setTimeout(function () {
                    if (
                        bookingRequest ===
                        currentRequest
                    ) {
                        hideBookingLoader();
                    }

                }, remaining);
            }
        });

        bookingRequest = currentRequest;
    }
    function updateFilterCount() {
        let count = 0;
        if ($('#bookingFromDate').val()) {
            count++;
        }
        if ($('#bookingToDate').val()) {
            count++;
        }
        if ($('#travelFromDate').val()) {
            count++;
        }
        if ($('#travelToDate').val()) {
            count++;
        }
        if (
            $('#bookingStatusFilter').val() &&
            $('#bookingStatusFilter').val() !== 'all'
        ) {
            count++;
        }
        if (
            $('#bookingSourceFilter').val() &&
            $('#bookingSourceFilter').val() !== 'all'
        ) {
            count++;
        }
        $('#activeFilterCount').text(count);
    }
    $(document).on(
        'click',
        '#showBookingFilters',
        function () {
            let panel =
                $('#bookingFilterPanel');

            if (panel.is(':visible')) {
                panel.hide();
            } else {
                panel.css('display', 'grid');
            }
        }
    );
    $(document).on(
        'change',
        '#bookingFromDate, #bookingToDate',
        function () {

            updateFilterCount();
            loadBookings(1);

        }
    );
    $(document).on(
        'change',
        '#travelFromDate, #travelToDate',
        function () {
            updateFilterCount();
            loadBookings(1);

        }
    );
    $(document).on(
        'change',
        '#bookingStatusFilter',
        function () {
            updateFilterCount();
            loadBookings(1);

        }
    );
    $(document).on(
        'change',
        '#bookingSourceFilter',
        function () {
            updateFilterCount();
            loadBookings(1);

        }
    );
    $(document).on(
        'click',
        '#bookingTypeTabs button',
        function (e) {
            e.preventDefault();
            selectedType = String(
                $(this).data('type')
            )
            .trim()
            .toLowerCase();
            loadBookings(1);
        }
    );
    $(document).on(
        'click',
        '#applyBookingFilter',
        function (e) {
            e.preventDefault();
            updateFilterCount();
            loadBookings(1);
        }
    );
    $(document).on(
        'click',
        '#resetBookingFilter',
        function (e) {
            e.preventDefault();
            selectedType = 'all';
            $('#bookingSearch').val('');
            $('#bookingFromDate').val('');
            $('#bookingToDate').val('');
            $('#travelFromDate').val('');
            $('#travelToDate').val('');
            $('#bookingStatusFilter').val('all');
            $('#bookingSourceFilter').val('all');
            $('#bookingFilterPanel').hide();
            updateFilterCount();
            loadBookings(1);
        }
    );
    updateFilterCount();
    initBookingDataTable();
});

//Source filter
$(document).ready(function () {
    let sourceRequest = null;
    let sortBy = 'amount';
    let sortOrder = 'desc';
    let sourceTable = null;
    let searchTimer = null;

    function createSourceLoader() {
        if ($('#sourceAjaxLoader').length) {
            return;
        }
        $('body').append(`
            <div id="sourceAjaxLoader">
                <div class="source-loader-box">
                    <div class="source-loader-spinner"></div>
                    <div class="source-loader-text">
                        Loading sources...
                    </div>
                </div>
            </div>
        `);
        $('<style>')
            .attr('id', 'sourceLoaderStyle')
            .html(`
                #sourceAjaxLoader{
                    position:fixed;
                    inset:0;
                    width:100%;
                    height:100%;
                    background:rgba(0,0,0,.35);
                    display:none;
                    align-items:center;
                    justify-content:center;
                    z-index:999999;
                }
                .source-loader-box{
                    background:#fff;
                    padding:22px 30px;
                    border-radius:12px;
                    text-align:center;
                    box-shadow:0 10px 35px rgba(0,0,0,.20);
                    min-width:180px;
                }
                .source-loader-spinner{
                    width:42px;
                    height:42px;
                    margin:0 auto 12px;
                    border:4px solid #e9ecef;
                    border-top:4px solid #ff5d43;
                    border-radius:50%;
                    animation:sourceLoaderSpin .8s linear infinite;
                }
                .source-loader-text{
                    font-size:14px;
                    font-weight:600;
                    color:#172033;
                }
                @keyframes sourceLoaderSpin{
                    0%{
                        transform:rotate(0deg);
                    }
                    100%{
                        transform:rotate(360deg);
                    }
                }
            `)
            .appendTo('head');
    }
    createSourceLoader();
    function showSourceLoader() {
        $('#sourceAjaxLoader').css('display', 'flex');
    }
    function hideSourceLoader() {
        $('#sourceAjaxLoader').fadeOut(150);
    }
    function initSourceDataTable() {
        if (!$('#sourceTable').length) {
            return;
        }
        if ($.fn.DataTable.isDataTable('#sourceTable')) {
            $('#sourceTable').DataTable().destroy();
        }
        sourceTable = $('#sourceTable').DataTable({
            pageLength: 20,
            lengthMenu: [20, 50, 100],
            searching: true,
            ordering: false,
            paging: true,
            info: true,
            autoWidth: false,
            scrollX: true,
            dom: '<"source-dt-top"lf>rt<"source-dt-bottom"ip>',
            language: {
                lengthMenu: 'Show _MENU_ entries',
                search: '',
                searchPlaceholder: 'Search source name...',
                info: 'Showing _START_–_END_ of _TOTAL_ sources',
                infoEmpty: 'Showing 0–0 of 0 sources',
                zeroRecords: 'No source data found.',
                paginate: {
                    previous: '‹',
                    next: '›'
                }
            },
            columnDefs: [
                {
                    orderable: false,
                    targets: 8
                }
            ]
        });
        $('#sourceSearch')
            .off('keyup.sourceSearch')
            .on('keyup.sourceSearch', function () {
                let value = this.value;
                clearTimeout(searchTimer);
                showSourceLoader();
                searchTimer = setTimeout(function () {
                    if (sourceTable) {
                        sourceTable
                            .search(value)
                            .draw();
                    }
                    setTimeout(function () {
                        hideSourceLoader();
                    }, 250);

                }, 100);
            });
    }
    function loadSources() {
        if (
            sourceRequest &&
            sourceRequest.readyState !== 4
        ) {
            sourceRequest.abort();
        }
        showSourceLoader();
        let startTime = Date.now();
        let currentRequest = $.ajax({
            url: base_url + '/admin/source-summary',
            type: 'GET',
            data: {
                from_date:
                    $('#sourceFromDate').val(),
                to_date:
                    $('#sourceToDate').val(),
                source_type:
                    $('#sourceType').val() || 'all',
                sort_by:
                    sortBy,
                sort_order:
                    sortOrder
            },
            success: function (response) {
                let html = $('<div>').html(response);
                if (
                    $.fn.DataTable.isDataTable(
                        '#sourceTable'
                    )
                ) {
                    $('#sourceTable')
                        .DataTable()
                        .destroy();

                    sourceTable = null;
                }
                $('#sourceTable tbody').html(
                    html.find(
                        '#sourceTable tbody'
                    ).html()
                );
                $('#sourceTable tfoot').html(
                    html.find(
                        '#sourceTable tfoot'
                    ).html()
                );
                initSourceDataTable();
                let searchValue =
                    $('#sourceSearch').val();
                if (
                    sourceTable &&
                    searchValue
                ) {
                    sourceTable
                        .search(searchValue)
                        .draw();
                }
            },
            error: function (xhr, status) {
                if (status !== 'abort') {
                    console.log(
                        xhr.responseText
                    );
                }

            },
            complete: function () {
                if (
                    sourceRequest !==
                    currentRequest
                ) {
                    return;
                }
                let elapsed =
                    Date.now() - startTime;
                let remaining =
                    Math.max(
                        300 - elapsed,
                        0
                    );
                setTimeout(function () {
                    if (
                        sourceRequest ===
                        currentRequest
                    ) {
                        hideSourceLoader();
                    }
                }, remaining);

            }
        });
        sourceRequest =
            currentRequest;
    }
    $(document).on(
        'change',
        '#sourceFromDate, #sourceToDate',
        function () {

            loadSources();

        }
    );
    $(document).on(
        'change',
        '#sourceType',
        function () {

            loadSources();

        }
    );
    $(document).on(
        'click',
        '#resetSourceFilter',
        function (e) {
            e.preventDefault();
            $('#sourceSearch').val('');
            $('#sourceFromDate').val('');
            $('#sourceToDate').val('');
            $('#sourceType').val('all');
            sortBy = 'amount';
            sortOrder = 'desc';
            loadSources();
        }
    );
    $(document).on(
        'click',
        '.source-sort',
        function (e) {
            e.preventDefault();
            let column =
                $(this).data('sort');
            if (sortBy === column) {
                sortOrder =
                    sortOrder === 'asc'
                        ? 'desc'
                        : 'asc';
            } else {
                sortBy = column;
                sortOrder = 'asc';
            }
            loadSources();
        }
    );
    $(document).on(
        'click',
        '#exportSources',
        function () {
            let rows = [];
            $('#sourceTable tbody tr')
                .each(function () {
                    let row = $(this);
                    if (
                        !row.find(
                            '.source-name strong'
                        ).length
                    ) {
                        return;
                    }
                    rows.push([
                        row.find(
                            '.source-name strong'
                        ).text().trim(),
                        row.find(
                            '.source-type'
                        ).text().trim(),
                        row.find(
                            '.booking-count'
                        ).text().trim(),
                        row.find(
                            '.count-pill.confirmed'
                        ).text().trim(),
                        row.find(
                            '.count-pill.cancelled'
                        ).text().trim(),
                        row.find(
                            '.source-amount'
                        ).text().trim(),
                        row.find(
                            'td:eq(7) strong'
                        ).text().trim()
                    ]);
                });
            if (!rows.length) {
                alert(
                    'No records to export.'
                );
                return;
            }
            let form = $('<form>', {
                method: 'POST',
                action:
                    base_url +
                    '/admin/source-summary-export'
            });
            form.append(
                $('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value:
                        $('meta[name="csrf-token"]')
                            .attr('content')
                })
            );
            form.append(
                $('<input>', {
                    type: 'hidden',
                    name: 'rows',
                    value:
                        JSON.stringify(rows)
                })
            );
            $('body').append(form);
            form.submit();

        }
    );
    initSourceDataTable();
});
//Agent booking summary filter
$(document).ready(function () {
    let agentRequest = null;
    let sortBy = 'bookings';
    let sortOrder = 'desc';

    const summaryUrl = base_url + '/admin/agent-booking-summary';
    function getFilters(page = 1) {
        return {
            page: page,
            search: $('#agentSearch').val(),
            from: $('#agentFromDate').val(),
            to: $('#agentToDate').val(),
            country: $('#agentCountryFilter').val(),
            status: $('#agentStatusFilter').val(),
            sort_by: sortBy,
            sort_order: sortOrder
        };
    }
    function updateSortButtons() {
        $('.agent-sort').removeClass('active asc desc');

        $('.agent-sort[data-sort="' + sortBy + '"]')
            .addClass('active ' + sortOrder);
    }
    function loadAgents(page = 1, showLoader = true) {
        if (agentRequest &&
            agentRequest.readyState !== 4) {
            agentRequest.abort();
        }
        if (showLoader) {
            $('#agentBookingAjaxLoader')
                .css('display', 'flex');
        }
        agentRequest = $.ajax({
            url: summaryUrl,
            type: 'GET',
            data: getFilters(page),
            success: function (response) {
                let html = $('<div>').html(response);
                let newRows = html.find('#agentBookingTable tbody tr');
                $('#cardAgents').html(html.find('#cardAgents').html());
                $('#cardBookings').html(html.find('#cardBookings').html());
                $('#cardSales').html(html.find('#cardSales').html());
                $('#cardWallet').html(html.find('#cardWallet').html());
                $('#activePeriod').html(
                    html.find('#activePeriod').html()
                );
                let table = $('#agentBookingTable').DataTable();
                table.clear();
                table.rows.add(newRows.toArray());
                table.draw();

                updateSortButtons();
            },
            error: function (xhr, status) {
                if (status !== 'abort') {
                    console.log(xhr.responseText);
                }
            },
            complete: function () {
                if (showLoader) {
                    $('#agentBookingAjaxLoader')
                        .fadeOut(150);
                }
            }
        });
    }
    //Search
    $(document).on(
        'keyup',
        '#agentSearch',
        function () {
            loadAgents(1, true);
        }
    );
    //Filters
    $(document).on(
        'change',
        '#agentFromDate, #agentToDate, #agentCountryFilter, #agentStatusFilter',
        function () {
            loadAgents(1, true);
        }
    );
    //Sorting
    $(document).on(
        'click',
        '.agent-sort',
        function () {
            let column =
                $(this).data('sort');
            if (sortBy === column) {
                sortOrder =
                    sortOrder === 'asc'
                        ? 'desc'
                        : 'asc';

            } else {
                sortBy = column;
                sortOrder = 'asc';
            }
            updateSortButtons();
            loadAgents(1, true);
        }
    );
    //Reset
    $(document).on(
        'click',
        '#resetAgentFilter',
        function (e) {
            e.preventDefault();
            $('#agentSearch').val('');
            $('#agentFromDate').val('');
            $('#agentToDate').val('');
            $('#agentCountryFilter').val('');
            $('#agentStatusFilter').val('');
            sortBy = 'bookings';
            sortOrder = 'desc';
            updateSortButtons();
            loadAgents(1, true);
        }
    );
    updateSortButtons();
});
//Agent subscriber filter
$(document).ready(function () {
    let request = null;
    $('body').append(`
        <div id="subscriptionAjaxLoader" style="
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.35);
            display:none;
            align-items:center;
            justify-content:center;
            z-index:999999;">
            <div style="
                background:#fff;
                padding:20px 28px;
                border-radius:10px;
                text-align:center;">

                <div class="subscription-loader-spinner"></div>
                <b>Loading subscriptions...</b>
            </div>
        </div>
    `);
    $('<style>').html(`
        .subscription-loader-spinner{
            width:40px;
            height:40px;
            margin:0 auto 10px;
            border:4px solid #eee;
            border-top:4px solid #ff5d43;
            border-radius:50%;
            animation:subscriptionSpin .8s linear infinite;
        }
        @keyframes subscriptionSpin{
            to{
                transform:rotate(360deg);
            }
        }
    `).appendTo('head');
    function loadSubscriptions(page = 1) {
        if (request && request.readyState !== 4) {
            request.abort();
        }
        $('#subscriptionAjaxLoader').css('display', 'flex');
        request = $.ajax({
            url: window.location.pathname,
            type: 'GET',
            data: {
                page: page,
                search: $('#subscriberSearch').val(),
                plan: $('#subscriptionPlanFilter').val(),
                status: $('#subscriptionStatusFilter').val(),
                from_date: $('#subscriptionFromDate').val(),
                to_date: $('#subscriptionToDate').val()
            },
            success: function (response) {
                let html = $('<div>').html(response);
                $('#subscriberTable tbody').html(
                    html.find('#subscriberTable tbody').html()
                );
                $('#subscriberTable tfoot').html(
                    html.find('#subscriberTable tfoot').html()
                );
                $('.table-footer').html(
                    html.find('.table-footer').html()
                );
                if (html.find('#subscriberTable tbody .text-center').length) {
                    $('#subscriberTable tfoot').hide();
                } else {
                    $('#subscriberTable tfoot').show();
                }
            },
            error: function (xhr, status) {
                if (status !== 'abort') {
                    console.log(xhr.responseText);
                }
            },
            complete: function () {
                $('#subscriptionAjaxLoader').fadeOut(150);
            }
        });
    }
    //Search
    $(document).on('keyup', '#subscriberSearch', function () {
        loadSubscriptions(1);
    });
    //Plan + Status
    $(document).on(
        'change',
        '#subscriptionPlanFilter, #subscriptionStatusFilter',
        function () {
            loadSubscriptions(1);
        }
    );
    //From + To Date
    $(document).on(
        'change',
        '#subscriptionFromDate, #subscriptionToDate',
        function () {
            loadSubscriptions(1);
        }
    );
    //Reset
    $(document).on('click', '#resetSubscriptionFilter', function (e) {
        e.preventDefault();
        $('#subscriberSearch').val('');
        $('#subscriptionPlanFilter').val('all');
        $('#subscriptionStatusFilter').val('all');
        $('#subscriptionFromDate').val('');
        $('#subscriptionToDate').val('');
        loadSubscriptions(1);
    });
});
//Supplier payments
$(document).ready(function () {
    let supplierRequest = null;
    let supplierTimer = null;
    function showSupplierLoader() {
        $('#supplierPaymentLoader')
            .css('display', 'flex');

    }
    function hideSupplierLoader() {
        $('#supplierPaymentLoader')
            .hide();
    }
    function getSupplierData() {
        return {
            slw:
                $.trim(
                    $('#slwNumberSearch').val()
                ),
            from_date:
                $('#supplierFromDate').val(),
            to_date:
                $('#supplierToDate').val(),
            source:
                $('#sourceFilter').val(),
            supplier:
                $('#supplierFilter').val(),
            payment_status:
                $('#paymentStatusFilter').val()
        };
    }
    function loadSupplierPayments(url) {
        if (
            supplierRequest &&
            supplierRequest.readyState !== 4
        ) {
            supplierRequest.abort();
        }
        showSupplierLoader();
        let requestUrl =
            url ||
            base_url +
            '/admin/supplier-payments';
        supplierRequest = $.ajax({
            url: requestUrl,
            type: 'GET',
            data: url
                ? {}
                : getSupplierData(),
            success: function (response) {
                let html =
                    $('<div>').html(response);
                // Stats
                $('.payment-stat-grid').html(
                    html
                        .find('.payment-stat-grid')
                        .html()
                );
                $('.supplier-payment-card').html(
                    html
                        .find('.supplier-payment-card')
                        .html()
                );
            },
            error: function (
                xhr,
                status
            ) {
                if (
                    status !== 'abort'
                ) {
                    console.log(
                        xhr.responseText
                    );
                }
            },
            complete: function () {
                hideSupplierLoader();
                supplierRequest = null;
            }
        });
    }
    $(document)
        .off(
            'click.supplier',
            '#searchSlwBooking'
        )
        .on(
            'click.supplier',
            '#searchSlwBooking',
            function (e) {

                e.preventDefault();

                loadSupplierPayments();

            }
        );
    $(document)
        .off(
            'keyup.supplier',
            '#supplierFromDate, #supplierToDate'
        )
        .on(
            'keyup.supplier',
            '#supplierFromDate, #supplierToDate',
            function () {
                clearTimeout(
                    supplierTimer
                );
                supplierTimer =
                    setTimeout(
                        function () {
                            loadSupplierPayments();

                        },
                        300
                    );
            }
        );
    $(document)
        .off(
            'change.supplier',
            '#sourceFilter, #supplierFilter, #paymentStatusFilter'
        )
        .on(
            'change.supplier',
            '#sourceFilter, #supplierFilter, #paymentStatusFilter',
            function () {

                loadSupplierPayments();

            }
        );
    $(document)
        .off(
            'keyup.supplier',
            '#sourceFilter, #supplierFilter, #paymentStatusFilter'
        )
        .on(
            'keyup.supplier',
            '#sourceFilter, #supplierFilter, #paymentStatusFilter',
            function () {
                clearTimeout(
                    supplierTimer
                );
                supplierTimer =
                    setTimeout(
                        function () {
                            loadSupplierPayments();

                        },
                        300
                    );
            }
        );
    $(document)
        .off(
            'click.supplier',
            '#resetSupplierFilter'
        )
        .on(
            'click.supplier',
            '#resetSupplierFilter',
            function (e) {
                e.preventDefault();
                $('#slwNumberSearch').val('');
                $('#supplierFromDate').val('');
                $('#supplierToDate').val('');
                $('#sourceFilter').val('');
                $('#supplierFilter').val('');
                $('#paymentStatusFilter').val('');
                loadSupplierPayments();
            }
        );
    $(document)
        .off(
            'click.supplier',
            '.agent-table-footer .page-link'
        )
        .on(
            'click.supplier',
            '.agent-table-footer .page-link',
            function (e) {
                let url =
                    $(this).attr('href');
                if (
                    !url ||
                    url === '#'
                ) {
                    e.preventDefault();
                    return;
                }
                e.preventDefault();
                showSupplierLoader();
                supplierRequest =
                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function (response) {
                            let html =
                                $('<div>').html(
                                    response
                                );
                            $('.payment-stat-grid')
                                .html(
                                    html
                                        .find(
                                            '.payment-stat-grid'
                                        )
                                        .html()
                                );
                            $('.supplier-payment-card')
                                .html(
                                    html
                                        .find(
                                            '.supplier-payment-card'
                                        )
                                        .html()
                                );

                        },
                        error: function (
                            xhr
                        ) {
                            console.log(
                                xhr.responseText
                            );

                        },
                        complete: function () {
                            hideSupplierLoader();
                            supplierRequest = null;

                        }
                    });
            }
        );
});
$(document)
    .off('click.supplierPayment', '.update-payment-button')
    .on('click.supplierPayment', '.update-payment-button', function () {

        let button = $(this);

        let bookingId = button.attr('data-booking-id') || '';
        let slw = button.attr('data-slw') || '—';
        let supplier = button.attr('data-supplier') || 'N/A';
        let currency = button.attr('data-currency') || 'THB';

        let net = parseFloat(
            button.attr('data-net')
        ) || 0;

        let paid = parseFloat(
            button.attr('data-paid')
        ) || 0;

        let due = Math.max(0, net - paid);

        $('#paymentBookingId').val(bookingId);

        $('#modalSlwNumber').text(slw);
        $('#modalSupplier').text(supplier);

        $('#modalNetAmount').text(
            currency + ' ' + net.toFixed(2)
        );

        $('#modalDueAmount').text(
            currency + ' ' + due.toFixed(2)
        );

        $('#paymentPersons').val('');

        $('#transferAmount').val('0');
        $('#guideAmount').val('0');
        $('#otherAmount').val('0');

        $('#paymentCurrency')
            .val(currency)
            .trigger('change');

        calculateSplitTotal();

        $('select[name="payment_mode"]').val('');

        $('input[name="transaction_reference"]').val('');

        $('input[name="payment_date"]').val(
            new Date().toISOString().split('T')[0]
        );

        $('select[name="payment_status"]').val('Paid');

        $('textarea[name="remark"]').val('');
    });
    function calculateSplitTotal() {
    let transfer =
        parseFloat($('#transferAmount').val()) || 0;
    let guide =
        parseFloat($('#guideAmount').val()) || 0;
    let other =
        parseFloat($('#otherAmount').val()) || 0;
    let total =
        transfer + guide + other;
    let currency =
        $('#paymentCurrency').val() || 'THB';
    //Total Paid Amount
    $('#paidAmount').val(
        total.toFixed(2)
    );
    //Split Amount Total
    $('#splitAmountTotal').text(
        currency + ' ' + total.toFixed(2)
    );
}
$(document)
.off(
    'input.supplierSplit change.supplierSplit',
    '#transferAmount, #guideAmount, #otherAmount, #paymentCurrency'
)
.on(
    'input.supplierSplit change.supplierSplit',
    '#transferAmount, #guideAmount, #otherAmount, #paymentCurrency',
    function () {

        calculateSplitTotal();
    }
);