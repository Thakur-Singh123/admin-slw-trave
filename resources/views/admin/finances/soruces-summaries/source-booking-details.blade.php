<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $sourceName }} Booking Statement</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet" />
        <link href="{{ asset('public/assets/css/source-booking-details.css') }}" rel="stylesheet" />
        {{-- DataTables --}}
        <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" />
        <style>
            #sourceBookingTable_wrapper {
                width: 100%;
            }
            #sourceBookingTable_wrapper .dt-layout-row {
                margin: 12px 0;
                align-items: center;
            }
            #sourceBookingTable_wrapper .dt-length label,
            #sourceBookingTable_wrapper .dt-search label {
                color: #8993a2;
                font-size: 9px;
            }
            #sourceBookingTable_wrapper .dt-length select,
            #sourceBookingTable_wrapper .dt-search input {
                height: 32px;
                border: 1px solid #e5e9ef;
                border-radius: 6px;
                box-shadow: none;
                font-size: 9px;
            }
            #sourceBookingTable_wrapper .dt-search input:focus,
            #sourceBookingTable_wrapper .dt-length select:focus {
                border-color: #ff9d8a;
                box-shadow: 0 0 0 3px rgb(255 86 53 / 8%);
                outline: 0;
            }
            #sourceBookingTable_wrapper .dt-info {
                color: #8993a2;
                font-size: 9px;
            }
            #sourceBookingTable_wrapper .dt-paging .page-link {
                color: #172033;
                border-color: #e5e9ef;
                box-shadow: none;
                font-size: 9px;
            }
            #sourceBookingTable_wrapper .dt-paging .page-item.active .page-link {
                color: #fff;
                background: #ff5d43;
                border-color: #ff5d43;
            }
            #sourceBookingTable_wrapper .dt-paging .page-item.disabled .page-link {
                color: #a8b0bb;
            }
            .booking-statement-table thead th {
                cursor: pointer;
                user-select: none;
            }
        </style>
    </head>
    <body>
        {{-- Toolbar --}}
        <div class="statement-toolbar">
            <a href="{{ route('admin.source.summary') }}" class="back-button">
                <i class="bi bi-arrow-left"></i>
                Back to Source Summary
            </a>
            <div>
                <button type="button" class="download-button" id="downloadStatement">
                    <i class="bi bi-download"></i>
                    Save PDF
                </button>
                <button type="button" class="print-button" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                    Print Statement
                </button>
            </div>
        </div>
        <main class="statement-page" id="statementDocument">
            {{-- Header --}}
            <header class="statement-header">
                <div class="company-brand">
                    <img src="{{ asset('public/assets/images/logo.png') }}" alt="Sun Leisure World" class="invoice-logo" />
                    <div>
                        <h1>Sun Leisure World Corporation</h1>
                        <p>Global B2B Travel Platform</p>
                    </div>
                </div>
                <div class="company-details">
                    <strong> SUN LEISURE WORLD CORPORATION </strong>
                    <span> GSTIN No: 0105554069478 </span>
                    <span> TAT License No: 14/0275 </span>
                    <span> Bangkok, Thailand </span>
                    <span> tech@sunleisureworld.com · slw.travel </span>
                </div>
            </header>
            {{-- Title --}}
            <section class="statement-title-row">
                <div>
                <span class="document-label"> SOURCE BOOKING STATEMENT </span>
                <h2>{{ $sourceName }}</h2>
                <p>Complete booking and revenue statement for the selected period.</p>
                </div>
                <div class="statement-meta">
                <dl>
                    <dt>Statement No.</dt>

                    <dd>{{ $statementNo }}</dd>
                </dl>
                <dl>
                    <dt>Generated Date</dt>
                    <dd>{{ now()->format('d M Y') }}</dd>
                </dl>
                <dl>
                    <dt>Period</dt>
                    <dd>
                        @if($fromDate && $toDate) {{ date('d M Y', strtotime($fromDate)) }} – {{ date('d M Y',
                        strtotime($toDate)) }} @elseif($fromDate) From {{ date('d M Y', strtotime($fromDate)) }}
                        @elseif($toDate) Up to {{ date('d M Y', strtotime($toDate)) }} @else All Dates @endif
                    </dd>
                </dl>
                <dl>
                    <dt>Currency</dt>
                    <dd>THB</dd>
                </dl>
                </div>
            </section>
            {{-- Summary --}}
            <section class="statement-summary">
                <article>
                <span> Total Bookings </span>
                <strong> {{ number_format($bookings->count()) }} </strong>
                <small> Source bookings </small>
                </article>
                <article>
                <span> Total Pax </span>
                <strong> {{ number_format($totalPax) }} </strong>
                <small> {{ number_format($totalAdult) }} adults · {{ number_format($totalChild) }} children </small>
                </article>
                <article>
                <span> Net Amount </span>
                <strong> THB {{ number_format($totalNet, 2) }} </strong>
                <small> Total supplier cost </small>
                </article>
                <article>
                <span> Sell Amount </span>
                <strong> THB {{ number_format($totalSell, 2) }} </strong>
                <small> Total selling value </small>
                </article>
            </section>
            {{-- Booking Table --}}
            <section class="booking-statement-table-wrap">
                <table class="booking-statement-table" id="sourceBookingTable" style="width: 100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Tour / Transfer Name</th>
                        <th>Travel Date</th>
                        <th>Pax</th>
                        <th>Net Amount</th>
                        <th>Sell Amount</th>
                        <th>Margin</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $index => $booking)
                    <tr>
                        {{-- Number --}}
                        <td>{{ $index + 1 }}</td>
                        {{-- Order --}}
                        <td>
                            <strong> SLW-{{ $booking['order_id'] }} </strong>
                            <small> {{ $booking['type'] }} </small>
                        </td>
                        {{-- Name --}}
                        <td>
                            <strong class="product-name"> {{ $booking['name'] }} </strong>
                        </td>
                        {{-- Travel --}}
                        <td>
                            @if(!empty($booking['travel_date'])) {{ date( 'd M Y', strtotime($booking['travel_date']) ) }}
                            @else N/A @endif
                        </td>
                        {{-- Pax --}}
                        <td>
                            <strong> {{ (int) $booking['adult'] + (int) $booking['child'] }} </strong>
                            <small> {{ $booking['adult'] }}A · {{ $booking['child'] }}C </small>
                        </td>
                        {{-- Net Amount --}}
                        <td>
                            <strong> THB {{ number_format( $booking['net_amount'], 2 ) }} </strong>
                            @if( isset($booking['adult_net']) && (float) $booking['adult_net'] > 0 )
                            <small style="display: block">
                            Adult: THB {{ number_format( $booking['adult_net'], 2 ) }}
                            </small>
                            @endif @if( isset($booking['child_net']) && (float) $booking['child_net'] > 0 )
                            <small style="display: block">
                            Child: THB {{ number_format( $booking['child_net'], 2 ) }}
                            </small>
                            @endif
                        </td>
                        {{-- Sell Amount --}}
                        <td>
                            <strong class="sell-amount"> THB {{ number_format( $booking['sell_amount'], 2 ) }} </strong>
                            @if( isset($booking['discount']) && (float) $booking['discount'] > 0 &&
                            strtolower(trim($booking['type'])) === 'tour' )
                            <small style="display: block; color: #198754">
                            Discount: + THB {{ number_format( $booking['discount'], 2 ) }}
                            </small>
                            @endif
                        </td>
                        {{-- Margin --}}
                        <td>
                            <strong
                            class="margin-amount"
                            style="{{ (float) $booking['margin'] < 0 ? 'color:#dc3545 !important;' : '' }}"
                            >
                            THB {{ number_format( $booking['margin'], 2 ) }}
                            </strong>
                        </td>
                        {{-- Status --}}
                        <td>
                            <span class="status-badge">
                            <i></i>
                            {{ $booking['status'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">No bookings found for this source.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">Grand Total</td>
                        <td>{{ number_format($totalPax) }} Pax</td>
                        <td>THB {{ number_format( $totalNet, 2 ) }}</td>
                        <td>THB {{ number_format( $totalSell, 2 ) }}</td>
                        <td>THB {{ number_format( $totalProfit, 2 ) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                </table>
            </section>
            {{-- Bottom --}}
            <section class="statement-bottom">
                <div class="statement-note">
                <h3>
                    <i class="bi bi-info-circle"></i>
                    Statement Note
                </h3>
                <p>
                    This is a system-generated source booking statement. Net amount represents supplier cost and sell
                    amount represents the final booking value.
                </p>
                </div>
                <div class="amount-summary">
                <dl>
                    <dt>Total Net Amount</dt>

                    <dd>THB {{ number_format( $totalNet, 2 ) }}</dd>
                </dl>
                <dl>
                    <dt>Total Sell Amount</dt>
                    <dd>THB {{ number_format( $totalSell, 2 ) }}</dd>
                </dl>
                <dl class="profit-row">
                    <dt>Total Margin</dt>
                    <dd>THB {{ number_format( $totalProfit, 2 ) }}</dd>
                </dl>
                </div>
            </section>
            {{-- Footer --}}
            <footer class="statement-footer">
                <p>
                For any assistance, contact
                <strong> tech@sunleisureworld.com </strong>
                </p>
                <p>This statement was generated electronically and does not require a signature.</p>
            </footer>
        </main>
        {{-- jQuery --}}
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        {{-- HTML2PDF --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        {{-- DataTables --}}
        <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function () {
                function initDataTable() {
                return $("#sourceBookingTable").DataTable({
                    pageLength: 20,
                    lengthMenu: [10, 20, 25, 50, 100],
                    searching: true,
                    ordering: true,
                    order: [[0, "asc"]],
                    info: true,
                    paging: true,
                    autoWidth: false,
                    columnDefs: [
                        {
                            orderable: false,
                            targets: 0,
                        },
                    ],
                });
                }
                let table = initDataTable();
                $("#printStatement").on("click", function () {
                window.print();
                });
                $("#downloadStatement").on("click", function () {
                let button = $(this);
                if (typeof html2pdf === "undefined") {
                    alert("PDF not found.");
                    return;
                }
                button.prop("disabled", true);
                button.html('<i class="bi bi-hourglass-split"></i> Generating...');
                table.destroy();
                setTimeout(function () {
                    let element = document.getElementById("statementDocument");
                    if (!element) {
                        alert("Statement document not found.");
                        table = initDataTable();
                        button.prop("disabled", false);
                        button.html('<i class="bi bi-download"></i> Save PDF');
                        return;
                    }
                    html2pdf()
                        .set({
                            margin: 5,
                            filename: "{{ Str::slug($sourceName) }}-booking-statement.pdf",
                            image: {
                            type: "png",
                            quality: 1,
                            },
                            html2canvas: {
                            scale: 2,
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: "#ffffff",
                            logging: false,
                            },
                            jsPDF: {
                            unit: "mm",
                            format: "a4",
                            orientation: "landscape",
                            },
                            pagebreak: {
                            mode: ["css", "legacy"],
                            },
                        })
                        .from(element)
                        .save()
                        .then(function () {
                            table = initDataTable();
                            button.prop("disabled", false);
                            button.html('<i class="bi bi-download"></i> Save PDF');
                        })
                }, 300);
                });
            });
        </script>
    </body>
</html>
