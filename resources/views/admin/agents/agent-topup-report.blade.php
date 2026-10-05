@extends('admin.layouts.master')
@section('content')
<div class="topup-report-page">
    <nav class="page-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">
            Dashboard
        </a>
        <i class="bi bi-chevron-right"></i>
        <span>
            Agent Top-up Report
        </span>

    </nav>
    <section class="page-heading topup-report-heading">
        <div>
            <p>
                Finance Reports
            </p>
            <h1>
                Agent Top-up Report
            </h1>
            <span>
                Track current-month agent wallet top-ups and monthly performance.
            </span>
        </div>
        <button
            type="button"
            class="report-export-button"
            id="exportTopupReport"
            onclick="window.location.href='{{ route('admin.agent.topup.export') }}'"
        >
            <i class="bi bi-download"></i>
            Export Report
        </button>
    </section>
    <section class="report-summary-grid">
        <article>
            <span class="summary-icon green">
                <i class="bi bi-wallet2"></i>
            </span>
            <div>
                <p>
                    Current Month Amount
                </p>
                <h2 id="currentMonthAmount">
                    ${{ number_format($currentMonthAmount, 2) }}
                </h2>
                <small>
                    <i class="bi bi-calendar3"></i>
                    {{ $currentMonthName }}
                </small>
            </div>
        </article>
        <article>
            <span class="summary-icon blue">
                <i class="bi bi-people"></i>
            </span>
            <div>
                <p>
                    Agents Topped Up
                </p>
                <h2 id="uniqueAgentCount">
                    {{ $uniqueAgentCount }}
                </h2>
                <small>
                    Unique agents this month
                </small>
            </div>
        </article>
        <article>
            <span class="summary-icon purple">
                <i class="bi bi-arrow-down-left-circle"></i>
            </span>
            <div>
                <p>
                    Total Transactions
                </p>
                <h2 id="topupTransactionCount">
                    {{ $topupTransactionCount }}
                </h2>
                <small>
                    Current month transactions
                </small>
            </div>
        </article>
        <article>
            <span class="summary-icon orange">
                <i class="bi bi-hourglass-split"></i>
            </span>
            <div>
                <p>
                    Pending Amount
                </p>
                <h2 id="pendingTopupAmount">
                    ${{ number_format($pendingTopupAmount, 2) }}
                </h2>
                <small>
                    {{ $pendingTransactionCount }}
                    transaction(s) pending
                </small>
            </div>
        </article>
    </section>
    <section class="content-card current-month-card">
        <div class="card-title-row">
            <div>
                <h3>
                    Current Month Top-ups
                </h3>
                <p>
                    Agent wallet top-ups recorded in {{ $currentMonthName }}.
                </p>
            </div>
            <span class="month-label">
                <i class="bi bi-calendar3"></i>
                {{ $currentMonthName }}
            </span>
        </div>
        <div class="report-toolbar">
            <div class="topup-search">
                <i class="bi bi-search"></i>
                <input
                    type="search"
                    id="topupSearch"
                    placeholder="Search agent, email, transaction or reference..."
                >
            </div>
            <div class="report-filters">
                <div class="date-filter">
                    <label for="topupFromDate">
                        From Date
                    </label>
                    <input
                        type="date"
                        id="topupFromDate"
                        class="form-control"
                        value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                    >
                </div>
                <div class="date-filter">
                    <label for="topupToDate">
                        To Date
                    </label>
                    <input
                        type="date"
                        id="topupToDate"
                        class="form-control"
                        value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                    >
                </div>
                <!-- <select
                    class="form-select"
                    id="topupStatusFilter"
                >
                    <option value="all">
                        All Status
                    </option>
                    <option value="completed">
                        Completed
                    </option>
                    <option value="pending">
                        Pending
                    </option>
                    <option value="failed">
                        Failed
                    </option>
                </select> -->
                <button
                    type="button"
                    class="reset-filter-button"
                    id="resetTopupFilter"
                >
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table
                class="table topup-report-table align-middle mb-0"
                id="topupReportTable"
            >
                <thead>
                    <tr>
                    <th>
                        Wallet ID
                    </th>
                        <th>
                            Transaction
                        </th>
                        <th>
                            Agent
                        </th>
                        <th>
                            Amount
                        </th>
                        <th>
                            Payment Method
                        </th>
                        <th>
                            Reference
                        </th>
                        <th>
                            Status
                        </th>
                        <th>
                            Date &amp; Time
                        </th>
                        <th>
                            Invoice
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topups as $topup)
                        @php
                            $agent = $agents[$topup->add_agent_id] ?? null;
                            $rawStatus = strtolower(
                                trim($topup->payment_status ?? '')
                            );

                            if (
                                in_array(
                                    $rawStatus,
                                    [
                                        'paid',
                                        'completed',
                                        'success',
                                        'successful'
                                    ]
                                )
                            ) {
                                $status = 'Completed';

                            } elseif (
                                in_array(
                                    $rawStatus,
                                    [
                                        'pending',
                                        'processing',
                                        'unpaid'
                                    ]
                                )
                            ) {
                                $status = 'Pending';

                            } elseif (
                                in_array(
                                    $rawStatus,
                                    [
                                        'failed',
                                        'cancelled',
                                        'canceled'
                                    ]
                                )
                            ) {
                                $status = 'Failed';
                            } else {

                                $status = 'Completed';
                            }
                            $statusClass = strtolower($status);
                            $amount = (float) (
                                $topup->deposit_amount ?? 0
                            );
                            $company = $agent->name ?? 'N/A';
                            $agentId =
                                'AGT-' . $topup->add_agent_id;
                            $email =
                                $agent->email ?? 'N/A';
                            $paymentMethod =
                                $topup->payment_mathod
                                ??
                                $topup->account_name
                                ??
                                'N/A';
                            $reference =
                                $topup->order_id
                                ??
                                'N/A';
                            $transactionId =
                                $topup->trasaction_number
                                ??
                                'TXN-' . $topup->id;
                            $transactionDate =
                                $topup->deposit_date;
                            $initials = collect(
                                explode(' ', $company)
                            )
                            ->filter()
                            ->map(
                                fn ($word) =>
                                    strtoupper(
                                        substr($word, 0, 1)
                                    )
                            )
                            ->take(2)
                            ->implode('');
                        @endphp
                        <tr
                            data-date="{{ $transactionDate }}"
                            data-status="{{ $statusClass }}"
                            data-amount="{{ number_format($amount, 2, '.', '') }}"
                            data-agent="{{ strtolower($company . ' ' . $agentId . ' ' . $email) }}"
                            data-search="{{ strtolower(
                                $company . ' ' .
                                $agentId . ' ' .
                                $email . ' ' .
                                $transactionId . ' ' .
                                $reference . ' ' .
                                $paymentMethod
                            ) }}"
                        >
                        <td>
                            <strong>
                                WAL-{{ $topup->id }}
                            </strong>
                        </td>
                            <td>
                                <strong>
                                    {{ $transactionId }}
                                </strong>
                                <small>
                                    Wallet Top-up
                                </small>
                            </td>
                            <td>
                                <div class="agent-cell">
                                    <span>
                                        {{ $initials ?: 'AG' }}
                                    </span>
                                    <div>
                                        <strong>
                                            {{ $company }}
                                        </strong>
                                        <small>
                                            {{ $agentId }}
                                            ·
                                            {{ $email }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong class="amount-value">
                                    ${{ number_format(
                                        $amount,
                                        2
                                    ) }}
                                </strong>
                                <small>
                                    USD
                                </small>

                            </td>
                            <td>
                                <span class="payment-method">
                                    <i class="bi bi-credit-card"></i>
                                    {{ $paymentMethod }}
                                </span>
                            </td>
                            <td>
                                <strong>
                                    {{ $reference }}
                                </strong>

                            </td>
                            <td>
                                <span
                                    class="transaction-status {{ $statusClass }}"
                                >
                                    <i></i>
                                    {{ $status }}
                                </span>
                            </td>
                            <td>
                                @if($transactionDate)
                                    <strong>
                                        {{ \Carbon\Carbon::parse(
                                            $transactionDate
                                        )->format('d M Y') }}
                                    </strong>
                                    <small>
                                        {{ \Carbon\Carbon::parse(
                                            $transactionDate
                                        )->format('h:i A') }}
                                    </small>
                                @else
                                    <strong>
                                        N/A
                                    </strong>
                                    <small>
                                        N/A
                                    </small>
                                @endif
                            </td>
                            <td>
                                <a
                                    href="{{ route(
                                        'admin.agent.wallet.invoice',
                                        $topup->id
                                    ) }}"
                                    target="_blank"
                                    class="invoice-link"
                                >
                                    <i class="bi bi-receipt"></i>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="8"
                                class="text-center py-5"
                            >
                                No agent top-ups found for
                                {{ $currentMonthName }}.

                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="topup-total-row">
                        <td colspan="3"></td>
                        <td class="total-amount-cell">
                            <span class="total-label-cell">
                                <span>Total Amount</span>
                                <small id="topupTotalNote">
                                    {{ $topupTransactionCount }} visible transactions
                                </small>
                            </span>
                            <strong id="visibleTopupTotal">
                                ${{ number_format($currentMonthAmount, 2) }}
                            </strong>
                            <small>USD</small>
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="table-footer">
            <p id="topupTableResult">
                Showing
                <strong>
                    {{ $topupTransactionCount }}
                </strong>
                transactions
            </p>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled">
                        <button
                            class="page-link"
                            type="button"
                        >
                            <i class="bi bi-chevron-left"></i>
                        </button>
                    </li>
                    <li class="page-item active">
                        <button
                            class="page-link"
                            type="button"
                        >
                            1
                        </button>
                    </li>
                    <li class="page-item disabled">
                        <button
                            class="page-link"
                            type="button"
                        >
                            <i class="bi bi-chevron-right"></i>

                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </section>
    <section class="report-chart-grid">
        <article class="content-card monthly-chart-card">
            <div class="card-title-row">
                <div>
                    <h3>
                        Month-wise Top-up Performance
                    </h3>
                    <p>
                        Total wallet amount added during the last 12 months.
                    </p>
                </div>
                <select
                    class="form-select"
                    id="chartYear"
                >
                    <option value="{{ now()->year }}">
                        {{ now()->year }}
                    </option>
                    <option value="{{ now()->subYear()->year }}">
                        {{ now()->subYear()->year }}
                    </option>
                </select>

            </div>
            <div class="chart-legend">
                <span>
                    <i class="amount"></i>
                    Top-up Amount (USD)
                </span>
                <span>
                    <i class="agents"></i>
                    Unique Agents
                </span>

            </div>
            <div class="chart-wrapper">
                <canvas id="monthlyTopupChart"></canvas>
            </div>
        </article>
        <article class="content-card payment-chart-card">
            <div class="card-title-row">
                <div>
                    <h3>
                        Payment Methods
                    </h3>
                    <p>
                        Current month payment distribution.
                    </p>
                </div>
            </div>
            <div class="donut-wrapper">
                <canvas id="paymentMethodChart"></canvas>
            </div>
            <div class="payment-breakdown">
                @php
                    $totalPaymentAmount = $paymentMethods->sum();
                @endphp
                @forelse($paymentMethods as $method => $amount)
                    @php
                        $percentage =
                            $totalPaymentAmount > 0
                                ? round(
                                    ($amount / $totalPaymentAmount) * 100
                                )
                                : 0;

                    @endphp
                    <div>
                        <span>
                            <i class="bank"></i>
                            {{ $method }}
                        </span>
                        <strong>
                            {{ $percentage }}%
                        </strong>
                    </div>
                @empty
                    <div>
                        <span>
                            No payment data
                        </span>
                        <strong>
                            0%
                        </strong>
                    </div>
                @endforelse
            </div>
        </article>
    </section>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    "use strict";
    let monthlyChart = null;
    let paymentChart = null;
    const monthlyCanvas = document.getElementById("monthlyTopupChart");
    const paymentCanvas = document.getElementById("paymentMethodChart");
    const chartYear = document.getElementById("chartYear");

    function createMonthlyChart() {
        if (!monthlyCanvas || typeof Chart === "undefined") {
            return;
        }
        if (monthlyChart) {
            monthlyChart.destroy();
        }
        monthlyChart = new Chart(monthlyCanvas, {
            type: "bar",
            data: {
                labels: @json($chartMonths),
                datasets: [
                    {
                        label: "Top-up Amount (USD)",
                        data: @json($chartAmounts),
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
                        data: @json($chartAgents),
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
                        titleFont: {
                            size: 11
                        },
                        bodyFont: {
                            size: 10
                        },
                        callbacks: {
                            label: function (context) {
                                if (
                                    context.dataset.yAxisID ===
                                    "amountAxis"
                                ) {
                                    return " Amount: " +
                                        new Intl.NumberFormat(
                                            "en-US",
                                            {
                                                style: "currency",
                                                currency: "USD",
                                                minimumFractionDigits: 2
                                            }
                                        ).format(context.parsed.y);
                                }
                                return " Agents: " +
                                    context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: "#8d97a7",
                            font: {
                                size: 9
                            }
                        }
                    },
                    amountAxis: {
                        position: "left",
                        beginAtZero: true,
                        grid: {
                            color:
                                "rgba(221, 226, 234, 0.65)"
                        },
                        ticks: {
                            color: "#8d97a7",
                            font: {
                                size: 9
                            },
                            callback: function (value) {
                                return "$" +
                                    Number(value)
                                        .toLocaleString("en-US");
                            }
                        }
                    },
                    agentAxis: {
                        position: "right",
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: "#8d97a7",
                            font: {
                                size: 9
                            },
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    function createPaymentChart() {
        if (!paymentCanvas || typeof Chart === "undefined") {
            return;
        }
        if (paymentChart) {
            paymentChart.destroy();
        }
        paymentChart = new Chart(paymentCanvas, {
            type: "doughnut",
            data: {
                labels: [
                    "Flywire Link",
                    "Bangkok Account",
                    "ICICI India Account (AMD)",
                    "Credit Wallet",
                    "Other"
                ],
                datasets: [
                    {
                        data: [
                            {{ $paymentMethods['Flywire Link'] ?? 0 }},
                            {{ $paymentMethods['Bangkok Account'] ?? 0 }},
                            {{ $paymentMethods['ICICI India Account (AMD)'] ?? 0 }},
                            {{ $paymentMethods['Credit Wallet'] ?? 0 }},
                            {{ $paymentMethods['Other'] ?? 0 }}
                        ],
                        backgroundColor: [
                            "#ff5335",
                            "#347bd7",
                            "#7654d4",
                            "#00a65a",
                            "#f59e0b"
                        ],
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
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: "#111c32",
                        padding: 10,
                        bodyFont: {
                            size: 10
                        },
                        callbacks: {
                            label: function (context) {
                                return " " +
                                    context.label +
                                    ": " +
                                    new Intl.NumberFormat(
                                        "en-US",
                                        {
                                            style: "currency",
                                            currency: "USD",
                                            minimumFractionDigits: 2
                                        }
                                    ).format(context.parsed);
                            }
                        }
                    }
                }
            }
        });
    }

    if (chartYear) {
        chartYear.addEventListener("change", function () {
            let year = this.value;
            $.ajax({
                url: "{{ url('admin/agent-topup-report') }}",
                type: "GET",
                data: {
                    chart_year: year
                },
                success: function (response) {
                    if (!monthlyChart) {
                        return;
                    }
                    monthlyChart.data.labels =
                        response.chartMonths;
                    monthlyChart.data.datasets[0].data =
                        response.chartAmounts;
                    monthlyChart.data.datasets[1].data =
                        response.chartAgents;
                    monthlyChart.update();
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                }
            });

        });
    }
    createMonthlyChart();
    createPaymentChart();
});
</script>
<style>
    .total-label-cell {
    display: block !important;
    width: auto !important;
    white-space: nowrap !important;
    text-align: left !important;
}

.total-label-cell span {
    display: block !important;
    white-space: nowrap !important;
}

.total-label-cell small {
    display: block !important;
    white-space: nowrap !important;
}
</style>
@endsection