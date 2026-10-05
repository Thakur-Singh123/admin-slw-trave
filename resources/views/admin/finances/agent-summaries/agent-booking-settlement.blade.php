<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $agent->name ?? 'Agent' }} | Booking Settlement</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet" />
        <link href="{{ asset('public/assets/css/source-booking-details.css') }}" rel="stylesheet" />
        <link href="{{ asset('public/assets/css/agent-booking-settlement.css') }}" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <style>
            /* Sorting buttons */
            .booking-statement-sort {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 0;
                margin: 0;
                border: 0;
                background: transparent;
                color: inherit;
                font: inherit;
                cursor: pointer;
                white-space: nowrap;
            }
            .booking-statement-sort i {
                font-size: 10px;
                color: #8994a4;
            }
            .booking-statement-sort.active i {
                color: inherit;
            }
            .booking-statement-sort.active.asc i {
                transform: rotate(180deg);
            }
            .booking-statement-sort.active.desc i {
                transform: rotate(0deg);
            }
            #statementDocument {
                background: #fff;
            }
            .booking-statement-table thead {
                display: table-header-group;
            }
            .booking-statement-table tr {
                page-break-inside: avoid;
            }
            @media print {
                .statement-toolbar {
                display: none !important;
                }
            }
        </style>
    </head>
    <body>
        {{-- Toolbar --}}
        <div class="statement-toolbar">
            <a href="{{ route('admin.agent.booking.summary') }}" class="back-button">
                <i class="bi bi-arrow-left"></i>
                Back to Agent Summary
            </a>
            <div>
                <button type="button" class="download-button" id="downloadStatement">
                    <i class="bi bi-download"></i>
                    Save PDF
                </button>
                <button type="button" class="print-button" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                    Print Settlement
                </button>
            </div>
        </div>
        {{-- PDF Document --}}
        <main class="statement-page" id="statementDocument">
            {{-- Header --}}
            <header class="statement-header">
                <div class="company-brand">
                <img src="{{ asset('public/assets/images/logo.png') }}" alt="Sun Leisure World logo" />
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
            {{-- Statement Title --}}
            <section class="statement-title-row">
                <div>
                <span class="document-label"> AGENT BOOKING SETTLEMENT </span>
                <h2>{{ $agent->name ?? 'N/A' }}</h2>
                <p>AGT-{{ $agent->add_agent_id }} · {{ $agent->name ?? 'N/A' }} · {{ $agent->country ?? 'N/A' }}</p>
                <p class="agent-contact-line">{{ $agent->email ?? 'N/A' }} · {{ $agent->phone ?? 'N/A' }}</p>
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
                    <dt>Booking Period</dt>
                    <dd>
                        {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} – {{
                        \Carbon\Carbon::parse($toDate)->format('d M Y') }}
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
                <strong> {{ $bookings->count() }} </strong>
                <small> Confirmed portal bookings </small>
                </article>
                <article>
                <span> Total Pax </span>
                <strong> {{ number_format($totalPax) }} </strong>
                <small> {{ $totalAdult }} adults · {{ $totalChild }} children </small>
                </article>
                <article>
                <span> Gross Sales </span>
                <strong> THB {{ number_format($totalSell, 2) }} </strong>
                <small> Total booking sell value </small>
                </article>
                <article>
                <span> Wallet Balance </span>
                <strong> USD {{ number_format($wallet, 2) }} </strong>
                <small> Current available wallet </small>
                </article>
            </section>
            {{-- Booking Table --}}
            <section class="booking-statement-table-wrap">
                <table class="booking-statement-table" id="settlementTable">
                <thead>
                    <tr>
                        <th>
                            <button type="button" class="booking-statement-sort" data-sort="index">
                            #

                            <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="booking-statement-sort" data-sort="order_id">
                            Order ID
                            <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="booking-statement-sort" data-sort="name">
                            Tour / Transfer / Hotel
                            <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="booking-statement-sort" data-sort="booking_date">
                            Booking Date

                            <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="booking-statement-sort" data-sort="travel_date">
                            Travel Date

                            <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="booking-statement-sort" data-sort="pax">
                            Pax

                            <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="booking-statement-sort" data-sort="net">
                            Net Cost
                            <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="booking-statement-sort" data-sort="sell">
                            Sell Amount

                            <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="booking-statement-sort" data-sort="margin">
                            Margin

                            <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="booking-statement-sort" data-sort="status">
                            Status
                            <i class="bi bi-arrow-down-up"></i>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody id="settlementBody">
                    @forelse($bookings as $index => $booking) @php $margin = (float) $booking['sell'] - (float)
                    $booking['net']; @endphp
                    <tr
                        data-index="{{ $index + 1 }}"
                        data-order-id="{{ strtolower($booking['order_id']) }}"
                        data-name="{{ strtolower($booking['name']) }}"
                        data-booking-date="{{ $booking['booking_date'] ? strtotime($booking['booking_date']) : 0 }}"
                        data-travel-date="{{ $booking['travel_date'] ? strtotime($booking['travel_date']) : 0 }}"
                        data-pax="{{ $booking['adult'] + $booking['child'] }}"
                        data-net="{{ $booking['net'] }}"
                        data-sell="{{ $booking['sell'] }}"
                        data-margin="{{ $margin }}"
                        data-status="{{ strtolower($booking['status']) }}"
                    >
                        {{-- # --}}
                        <td>{{ $index + 1 }}</td>
                        {{-- Order --}}
                        <td>
                            <strong> {{ $booking['order_id'] }} </strong>

                            <small> {{ $booking['type'] }} </small>
                        </td>
                        {{-- Product --}}
                        <td>
                            <strong class="product-name"> {{ $booking['name'] }} </strong>
                        </td>
                        {{-- Booking Date --}}
                        <td>
                            {{ $booking['booking_date'] ? \Carbon\Carbon::parse( $booking['booking_date'] )->format('d M Y')
                            : 'N/A' }}
                        </td>
                        {{-- Travel Date --}}
                        <td>
                            {{ $booking['travel_date'] ? \Carbon\Carbon::parse( $booking['travel_date'] )->format('d M Y') :
                            'N/A' }}
                        </td>
                        {{-- Pax --}}
                        <td>
                            <strong> {{ $booking['adult'] + $booking['child'] }} </strong>
                            <small> {{ $booking['adult'] }}A · {{ $booking['child'] }}C </small>
                        </td>
                        {{-- Net --}}
                        <td>THB {{ number_format( $booking['net'], 2 ) }}</td>
                        {{-- Sell --}}
                        <td>
                            <strong class="sell-amount"> THB {{ number_format($booking['sell'], 2) }} </strong>

                            @if(($booking['discount'] ?? 0) > 0 && strtolower($booking['type']) === 'tour')
                            <small style="display: block; color: #198754">
                            Discount: + THB {{ number_format($booking['discount'], 2) }}
                            </small>
                            @endif
                        </td>
                        {{-- Margin --}}
                        <td>
                            <strong
                            class="margin-amount"
                            style="{{ $margin < 0
                                            ? 'color:#dc3545 !important;'
                                            : ''
                                        }}"
                            >
                            THB {{ number_format( $margin, 2 ) }}
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
                        <td colspan="10" class="text-center">No bookings found for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
                {{-- Grand Total --}}
                <tfoot>
                    <tr>
                        <td colspan="5">Grand Total</td>
                        <td>{{ number_format($totalPax) }} Pax</td>
                        <td>THB {{ number_format( $totalNet, 2 ) }}</td>
                        <td>THB {{ number_format( $totalSell, 2 ) }}</td>
                        <td>THB {{ number_format( $totalMargin, 2 ) }}</td>
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
                    Settlement Note
                </h3>
                <p>
                    This system-generated statement includes bookings created by this agent during the selected booking
                    period. Net cost is the supplier cost; sell amount is the amount charged for the booking.
                </p>
                </div>
                <div class="amount-summary">
                <dl>
                    <dt>Total Net Cost</dt>
                    <dd>THB {{ number_format($totalNet, 2) }}</dd>
                </dl>
                <dl>
                    <dt>Total Gross Sales</dt>
                    <dd>THB {{ number_format($totalSell, 2) }}</dd>
                </dl>
                <dl class="profit-row">
                    <dt>Total Margin</dt>
                    <dd
                        style="{{ $totalMargin < 0
                                ? 'color:#dc3545 !important;'
                                : ''
                            }}"
                    >
                        THB {{ number_format( $totalMargin, 2 ) }}
                    </dd>
                </dl>
                </div>
            </section>
            {{-- Footer --}}
            <footer class="statement-footer">
                <p>
                For any assistance, contact
                <strong> tech@sunleisureworld.com </strong>
                </p>
                <p>This settlement was generated electronically and does not require a signature.</p>
            </footer>
        </main>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script>
            $(document).ready(function () {
                let sortColumn = null;
                let sortDirection = "asc";

                $(document).on("click", ".booking-statement-sort", function () {
                let column = $(this).data("sort");

                if (sortColumn === column) {
                    sortDirection = sortDirection === "asc" ? "desc" : "asc";
                } else {
                    sortColumn = column;
                    sortDirection = "asc";
                }
                $(".booking-statement-sort").removeClass("active asc desc");
                $(this).addClass("active " + sortDirection);
                let rows = $("#settlementBody tr").get();
                rows.sort(function (a, b) {
                    let aValue = $(a).data(column);
                    let bValue = $(b).data(column);
                    if (
                        column === "index" ||
                        column === "pax" ||
                        column === "net" ||
                        column === "sell" ||
                        column === "margin" ||
                        column === "booking_date" ||
                        column === "travel_date"
                    ) {
                        aValue = parseFloat(aValue) || 0;
                        bValue = parseFloat(bValue) || 0;
                    } else {
                        aValue = String(aValue ?? "").toLowerCase();
                        bValue = String(bValue ?? "").toLowerCase();
                    }
                    if (aValue < bValue) {
                        return sortDirection === "asc" ? -1 : 1;
                    }
                    if (aValue > bValue) {
                        return sortDirection === "asc" ? 1 : -1;
                    }
                    return 0;
                });
                $.each(rows, function (index, row) {
                    $("#settlementBody").append(row);
                });
                });

                $("#downloadStatement").on("click", function () {
                let button = $(this);
                if (typeof html2pdf === "undefined") {
                    alert("PDF not found.");
                    return;
                }
                button.prop("disabled", true).html('<i class="bi bi-hourglass-split"></i> Generating...');
                let element = document.getElementById("statementDocument");
                html2pdf()
                    .set({
                        margin: 5,
                        filename: "{{ \Illuminate\Support\Str::slug($agent->name ?? 'agent') }}-booking-settlement.pdf",
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
                        button.prop("disabled", false).html('<i class="bi bi-download"></i> Save PDF');
                    })
                    .catch(function (error) {
                        console.error("PDF ERROR:", error);
                        button.prop("disabled", false).html('<i class="bi bi-download"></i> Save PDF');
                        alert("PDF not found");
                    });
                });
            });
        </script>
    </body>
</html>
