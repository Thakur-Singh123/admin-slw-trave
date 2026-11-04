@php
    $money = fn($amount, $currency) => $currency . ' ' . number_format((float) $amount, 2);
    $cur = $statement['currency'];
    $count = count($statement['bookings']);
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $statement['invoice_no'] }} | Supplier Payment Statement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="{{ asset('public/assets/css/supplier-payment-invoice.css') }}?v=1.1" rel="stylesheet">
</head>
<body>

<div class="invoice-toolbar">
    <button type="button" class="toolbar-back" onclick="window.location.href='{{ url('admin/supplier-payments') }}'">
        <i class="bi bi-arrow-left"></i>
        <span>Back</span>
    </button>

    <div class="toolbar-actions">
        <button type="button" id="downloadInvoice">
            <i class="bi bi-download"></i>
            Download PDF
        </button>

        <button type="button" class="primary" id="printInvoice">
            <i class="bi bi-printer"></i>
            Print Statement
        </button>
    </div>
</div>

<main class="invoice-sheet" id="supplierInvoice">
    <header class="invoice-header">
        <div class="brand-area">
            <img src="https://slw.travel/assets/images/logo.png" alt="SLW Travel Logo">

            <div>
                <span class="document-type">SUPPLIER FINANCE</span>
                <h1>Payment &amp; P&amp;L Statement</h1>
                <p>Booking settlement and profitability summary</p>
            </div>
        </div>

        <div class="invoice-meta">
            <span>Statement Number</span>
            <strong>{{ $statement['invoice_no'] }}</strong>
            <small>Issued on {{ $statement['statement_date'] }}</small>
        </div>
    </header>

    <section class="statement-overview">
        <div class="overview-party">
            <span class="eyebrow">Supplier Details</span>
            <h2>{{ $statement['supplier_name'] }}</h2>

            <p><i class="bi bi-hash"></i> {{ $statement['supplier_code'] }}</p>
            <p><i class="bi bi-envelope"></i> {{ $statement['supplier_email'] }}</p>
            <p><i class="bi bi-telephone"></i> {{ $statement['supplier_phone'] }}</p>
        </div>

        <div class="overview-period">
            <span class="eyebrow">Statement Period</span>

            <div class="period-box">
                <i class="bi bi-calendar3"></i>
                <div>
                    <strong>{{ $statement['period_from'] }} – {{ $statement['period_to'] }}</strong>
                    <small>Source: {{ $statement['source'] }}</small>
                </div>
            </div>
        </div>
    </section>

    <section class="hero-summary">
        <div class="hero-primary">
            <span>Total Supplier Payable</span>
            <strong>{{ $money($supplierCost, $cur) }}</strong>
            <small>{{ $count }} booking transactions</small>
        </div>

        <div class="hero-payment">
            <div>
                <span>Paid</span>
                <strong>{{ $money($totalPaid, $cur) }}</strong>
            </div>

            <div>
                <span>Balance Due</span>
                <strong>{{ $money($supplierDue, $cur) }}</strong>
            </div>

            <span class="payment-status {{ strtolower(str_replace(' ', '-', $statement['payment_status'])) }}">
                <i></i>
                {{ $statement['payment_status'] }}
            </span>
        </div>
    </section>

    <section class="document-section">
        <div class="section-heading">
            <div>
                <span>01</span>
                <div>
                    <h3>Booking Settlement Details</h3>
                    <p>Booking-wise supplier cost, selling price and profit</p>
                </div>
            </div>

            <strong>{{ $count }} Bookings</strong>
        </div>

       <div class="table-responsive">
            @if($count > 0)
            <table class="booking-table" id="invoiceBookingTable">
                <thead>
                    <tr>
                        <th class="sn-col">#</th>
                        <th>Booking &amp; Service</th>
                        <th>Customer / Pax</th>
                        <th>Net Cost</th>
                        <th>Sale Price</th>
                        <th>Gross Profit</th>
                        <th>Payment</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($statement['bookings'] as $booking)
                        <tr>
                            <td class="sn-cell">{{ $loop->iteration }}.</td>
                            <td>
                                <strong class="service-name">{{ $booking['service'] }}</strong>
                                <span class="booking-reference">
                                    {{ $booking['booking_id'] }} · Travel {{ $booking['travel_date'] }}
                                </span>
                                @if(!empty($booking['payment_person']))
                                    <span class="booking-reference">
                                        Payment Person: {{ $booking['payment_person'] }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $booking['customer'] }}</strong>
                                <span>{{ $booking['pax'] }}</span>
                            </td>

                            <td class="amount-cell net" data-order="{{ $booking['net_cost'] }}">
                                {{ $money($booking['net_cost'], $cur) }}
                            </td>

                            <td class="amount-cell sale" data-order="{{ $booking['sale_price'] }}">
                                {{ $money($booking['sale_price'], $cur) }}
                            </td>

                            <td class="amount-cell profit" data-order="{{ $booking['profit'] }}">
                                {{ $money($booking['profit'], $cur) }}
                            </td>

                            <td data-order="{{ $booking['paid'] }}">
                                <strong class="paid-amount">{{ $money($booking['paid'], $cur) }}</strong>
                                <span class="due-amount">Due: {{ $money($booking['due'], $cur) }}</span>
                                    @if(!empty($booking['paid_date']))
                                        <span class="paid-date">Paid on {{ $booking['paid_date'] }}</span>
                                    @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="3">Booking Total</th>
                        <th>{{ $money($supplierCost, $cur) }}</th>
                        <th>{{ $money($grossSales, $cur) }}</th>
                        <th>{{ $money($grossProfit, $cur) }}</th>
                        <th>{{ $money($totalPaid, $cur) }}</th>
                    </tr>
                </tfoot>
            </table>
            @else
                <p class="text-center py-4">No bookings found for this period.</p>
            @endif
        </div>

    </section>

    <section class="document-section profit-section">
        <div class="section-heading">
            <div>
                <span>02</span>
                <div>
                    <h3>Profit &amp; Loss Summary</h3>
                    <p>Revenue, direct cost and net profitability</p>
                </div>
            </div>

            <span class="profit-period">Selected Period</span>
        </div>

        <div class="profit-layout">
            <div class="profit-calculation">
                <div>
                    <span>Gross Sales Revenue</span>
                    <strong>{{ $money($grossSales, $cur) }}</strong>
                </div>

                <div class="deduction">
                    <span>Less: Supplier Net Cost</span>
                    <strong>− {{ $money($supplierCost, $cur) }}</strong>
                </div>

                <div class="gross-profit-row">
                    <span>Gross Profit</span>
                    <strong>{{ $money($grossProfit, $cur) }}</strong>
                </div>

                <div class="deduction">
                    <span>Less: Other Expenses</span>
                    <strong>− {{ $money($statement['other_expenses'], $cur) }}</strong>
                </div>

                <div class="net-profit-row">
                    <span>Net Profit</span>
                    <strong>{{ $money($netProfit, $cur) }}</strong>
                </div>
            </div>

            <div class="margin-panel">
                <div class="margin-circle">
                    <strong>{{ number_format($netMargin, 2) }}%</strong>
                    <span>Net Margin</span>
                </div>

                <div class="margin-stat">
                    <span>Gross Margin</span>
                    <strong>{{ number_format($grossMargin, 2) }}%</strong>
                </div>

                <div class="margin-stat">
                    <span>Profit Per Booking</span>
                    <strong>{{ $money($netProfit / max(1, $count), $cur) }}</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="document-section">
        <div class="section-heading">
            <div>
                <span>03</span>
                <div>
                    <h3>Supplier Payment Summary</h3>
                    <p>Settlement status and payment reference</p>
                </div>
            </div>
        </div>

        <div class="payment-grid">
            <div>
                <span>Total Payable</span>
                <strong>{{ $money($supplierCost, $cur) }}</strong>
            </div>

            <div>
                <span>Amount Paid</span>
                <strong class="green">{{ $money($totalPaid, $cur) }}</strong>
            </div>

            <div>
                <span>Outstanding Due</span>
                <strong class="orange">{{ $money($supplierDue, $cur) }}</strong>
            </div>

            <div>
                <span>Payment Mode</span>
                <strong>{{ $statement['payment_mode'] }}</strong>
            </div>

            <div>
                <span>Payment Reference</span>
                <strong>{{ $statement['payment_reference'] }}</strong>
            </div>

            <div>
                <span>Last Payment Date</span>
                <strong>{{ $statement['payment_date'] }}</strong>
            </div>
        </div>
    </section>

    <aside class="statement-note">
        <i class="bi bi-info-circle"></i>

        <div>
            <strong>Finance Note</strong>
            <p>
                This statement shows booking sales, supplier costs and internal profitability.
                The outstanding supplier balance should be settled against the stated booking references.
            </p>
        </div>
    </aside>

    <footer class="invoice-footer">
        <div>
            <strong>Sun Leisure World Corporation</strong>
            <p>This is a computer-generated supplier finance statement.</p>
        </div>
        <div class="footer-support">
            <span>Finance assistance</span>
            <strong>tech@sunleisureworld.com</strong>
        </div>
    </footer>
</main>
<script src="{{ asset('public/assets/js/supplier-payment-invoice.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
$(function () {
    if (!$('#invoiceBookingTable').length) return;
    const table = $('#invoiceBookingTable').DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [],
        autoWidth: false,
        columnDefs: [{ targets: 0, orderable: false, searchable: false }]
    });

    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();
    window.invoiceShowAllRows = function () { table.page.len(-1).draw(); };
    window.invoiceRestoreRows = function () { table.page.len(10).draw(); };

    window.addEventListener('beforeprint', function () {
        if (typeof table !== 'undefined') table.page.len(-1).draw();
        $('#invoiceBookingTable tfoot').css('display', 'table-row-group');
    });

    window.addEventListener('afterprint', function () {
        if (typeof table !== 'undefined') table.page.len(10).draw();
        $('#invoiceBookingTable tfoot').css('display', 'table-footer-group');
    });
});
</script>
</body>
</html>