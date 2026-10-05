<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AddAgent;
use App\Models\AgentWallet;
use App\Helpers\ExportHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgentReportController extends Controller
{
    //Function for agent topup report
    public function agent_topup_report(Request $request) {
        //Filter
        if ($request->filled('chart_year')) {
            $year = (int) $request->chart_year;
            $chartMonths  = [];
            $chartAmounts = [];
            $chartAgents  = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthData = AgentWallet::query()
                    ->where('isactive', 1)
                    ->where('deposit_amount', '>', 0)
                    ->whereYear('deposit_date', $year)
                    ->whereMonth('deposit_date', $month)
                    ->get();
                $chartMonths[] = Carbon::create(
                    $year,
                    $month,
                    1
                )->format('M');

                $chartAmounts[] = round(
                    $monthData->sum(function ($item) {
                        return (float) ($item->deposit_amount ?? 0);
                    }),
                    2
                );
                $chartAgents[] = $monthData
                    ->pluck('add_agent_id')
                    ->filter()
                    ->unique()
                    ->count();
            }
            return response()->json([
                'year'         => $year,
                'chartMonths'  => $chartMonths,
                'chartAmounts' => $chartAmounts,
                'chartAgents'  => $chartAgents,
            ]);
        }
        //Current Month
        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();
        //Top-up Query
       $query = AgentWallet::query()
        ->where('isactive', 1)
        ->where('deposit_amount', '>', 0);
        if (
            !$request->filled('from_date') &&
            !$request->filled('to_date')
        ) {
            $query->whereBetween('deposit_date', [
                $startOfMonth,
                $endOfMonth
            ]);
        }
        //Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $agentIds = AddAgent::where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            })->pluck('add_agent_id');
            $query->where(function ($q) use ($search, $agentIds) {
                $q->where(
                    'trasaction_number',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'payment_mathod',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'account_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'order_id',
                    'like',
                    "%{$search}%"
                )
                ->orWhereIn(
                    'add_agent_id',
                    $agentIds
                );

            });
        }
        //From Date
        if ($request->filled('from_date')) {
            $query->whereDate(
                'deposit_date',
                '>=',
                $request->from_date
            );
        }
        //To Date
        if ($request->filled('to_date')) {
            $query->whereDate(
                'deposit_date',
                '<=',
                $request->to_date
            );
        }
        //Status
        if (
            $request->filled('status')
            &&
            $request->status !== 'all'
        ) {
            $status = strtolower(
                trim($request->status)
            );
            if ($status === 'completed') {
                $query->whereIn(
                    'payment_status',
                    [
                        'paid',
                        'completed',
                        'success',
                        'successful'
                    ]
                );

            } elseif ($status === 'pending') {
                $query->whereIn(
                    'payment_status',
                    [
                        'pending',
                        'processing',
                        'unpaid'
                    ]
                );
            } elseif ($status === 'failed') {
                $query->whereIn(
                    'payment_status',
                    [
                        'failed',
                        'cancelled',
                        'canceled'
                    ]
                );
            }
        }
        $topups = $query->orderBy('deposit_date', 'DESC')->get();
        //Current Month Summary
        $allCurrentMonthTopups = AgentWallet::query()
            ->where('isactive', 1)
            ->where('deposit_amount', '>', 0)
            ->whereBetween('deposit_date', [
                $startOfMonth,
                $endOfMonth
            ])
            ->get();
        $currentMonthAmount = $allCurrentMonthTopups->sum(
            function ($item) {
                return (float) ($item->deposit_amount ?? 0);
            }
        );
        $uniqueAgentCount = $allCurrentMonthTopups
            ->pluck('add_agent_id')
            ->filter()
            ->unique()
            ->count();
        $topupTransactionCount =
            $allCurrentMonthTopups->count();

        //Pending Summary
        $pendingTransactions = $allCurrentMonthTopups->filter(
            function ($item) {
                $status = strtolower(
                    trim($item->payment_status ?? '')
                );
                return in_array(
                    $status,
                    [
                        'pending',
                        'processing',
                        'unpaid'
                    ]
                );
            }
        );
        $pendingTopupAmount = $pendingTransactions->sum(
            function ($item) {
                return (float) ($item->deposit_amount ?? 0);
            }
        );
        $pendingTransactionCount =
            $pendingTransactions->count();
        $agentIds = $topups
            ->pluck('add_agent_id')
            ->filter()
            ->unique();
        $agents = AddAgent::whereIn(
            'add_agent_id',
            $agentIds
        )
            ->get()
            ->keyBy('add_agent_id');
        //Payment Methods
        $paymentMethods = collect([
            'Flywire Link'              => 0,
            'Bangkok Account'           => 0,
            'ICICI India Account (AMD)' => 0,
            'Credit Wallet'             => 0,
            'Other'                     => 0,
        ]);
        foreach ($allCurrentMonthTopups as $topup) {
            $method = trim(
                $topup->payment_mathod
                ?? $topup->account_name
                ?? ''
            );
            $amount = (float) (
                $topup->deposit_amount ?? 0
            );
            if (
                $method === 'Flywire Link'
                || $method === 'Bangkok Account'
                || $method === 'ICICI India Account (AMD)'
                || $method === 'Credit Wallet'
            ) {
                $paymentMethods[$method] += $amount;

            } else {
                $paymentMethods['Other'] += $amount;
            }
        }
        $chartYears = range(
            2024,
            now()->year + 1
        );

        $selectedChartYear = now()->year;
        $chartMonths  = [];
        $chartAmounts = [];
        $chartAgents  = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthData = AgentWallet::query()
                ->where('isactive', 1)
                ->where('deposit_amount', '>', 0)
                ->whereYear(
                    'deposit_date',
                    $selectedChartYear
                )
                ->whereMonth(
                    'deposit_date',
                    $month
                )
                ->get();
            $chartMonths[] = Carbon::create(
                $selectedChartYear,
                $month,
                1
            )->format('M');
            $chartAmounts[] = round(
                $monthData->sum(function ($item) {
                    return (float) (
                        $item->deposit_amount ?? 0
                    );
                }),
                2
            );
            $chartAgents[] = $monthData
                ->pluck('add_agent_id')
                ->filter()
                ->unique()
                ->count();
        }
        $currentMonthName =
            now()->format('F Y');

        return view('admin.agents.agent-topup-report', compact('topups','agents','currentMonthAmount','uniqueAgentCount','topupTransactionCount','pendingTopupAmount','pendingTransactionCount','paymentMethods','chartMonths','chartAmounts','chartAgents','chartYears','selectedChartYear','currentMonthName'));
    }

    //Function for agent top-up report export
    public function agent_topup_export() {
        //Current month top-ups
        $topups = AgentWallet::where('isactive', 1)
            ->where('deposit_amount', '>', 0)
            ->whereBetween('deposit_date', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])
                ->orderBy('deposit_date', 'DESC')
                ->get();
        //Agents
        $agentIds = $topups
            ->pluck('add_agent_id')
            ->filter()
            ->unique();

        $agents = AddAgent::whereIn(
            'add_agent_id',
            $agentIds
        )
            ->get()
            ->keyBy('add_agent_id');
        $rows = [];
        foreach ($topups as $topup) {
            $agent = $agents[$topup->add_agent_id] ?? null;
            $status = trim($topup->payment_status ?? '');
            $paymentMethod = trim($topup->payment_mathod ?? $topup->account_name ?? 'N/A');
            $rows[] = [
                'WAL-' . $topup->id,
                $topup->trasaction_number ?? 'TXN-' . $topup->id,
                $agent->name ?? 'N/A',
                $agent->email ?? 'N/A',
                '$' . number_format(
                    (float) ($topup->deposit_amount ?? 0),
                    2
                ),
                $paymentMethod,
                $topup->order_id ?? 'N/A',
                $status !== '' ? $status : 'N/A',
                $topup->deposit_date ? Carbon::parse($topup->deposit_date)->format('d M Y, h:i A') : 'N/A',
            ];
        }
        return ExportHelper::download(
            'agent-topup-report-' . now()->format('F-Y') . '.xlsx',
            'AGENT TOP-UP REPORT - ' .
            now()->format('F Y'),
            [
                'Wallet ID',
                'Transaction ID',
                'Agent',
                'Email',
                'Amount',
                'Payment Method',
                'Order ID',
                'Status',
                'Date & Time'
            ],
            $rows
        );
    }
}