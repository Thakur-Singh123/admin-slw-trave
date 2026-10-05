<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AddAgent;
use App\Models\AgentWallet;
use App\Helpers\ExportHelper;
use App\Mail\AgentActivatedMail;
use App\Mail\AgentBulkEmailMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgentController extends Controller
{
    //Function for all agents
    public function index(Request $request) {
        $query = AddAgent::query();
        $clear = $request->has('clear');
        if (!$clear) {
            //Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('contact_person', 'LIKE', "%{$search}%")
                        ->orWhere('username', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('telephone', 'LIKE', "%{$search}%")
                        ->orWhere('address', 'LIKE', "%{$search}%")
                        ->orWhere('city', 'LIKE', "%{$search}%")
                        ->orWhere('state', 'LIKE', "%{$search}%")
                        ->orWhere('country', 'LIKE', "%{$search}%")
                        ->orWhere('business_type', 'LIKE', "%{$search}%")
                        ->orWhere('gst_number', 'LIKE', "%{$search}%")
                        ->orWhere('IATA_Code', 'LIKE', "%{$search}%");
                });
            }
            //Email
            if ($request->filled('email')) {
                $query->where('email', 'LIKE', '%' . $request->email . '%');
            }
            //Date
            if ($request->filled('from_date')) {
                $query->whereDate('date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('date', '<=', $request->to_date);
            }
            //First load = today
            if (
                !$request->filled('search') &&
                !$request->filled('email') &&
                !$request->filled('from_date') &&
                !$request->filled('to_date') &&
                !$request->filled('status') &&
                !$request->filled('country') &&
                !$request->filled('amount') &&
                !$request->filled('page')
            ) {
                $query->whereDate('date', now()->format('Y-m-d'));
            }
            //Status
            if ($request->filled('status') && $request->status != 'all') {

                if ($request->status == '1') {
                    $query->where('isactive', 1);

                } elseif ($request->status == '0') {
                    $query->where('isactive', 0);

                } elseif ($request->status == '2') {
                    $query->where('isactive', '>', 1);
                }
            }
            //Country
            if ($request->filled('country') && $request->country != 'all') {
                $query->whereRaw(
                    'LOWER(country) = ?',
                    [strtolower($request->country)]
                );
            }
            //Amount Sorting
            if ($request->amount == 'low_high') {
                $query->leftJoinSub(
                    DB::table('agent_wallets')
                        ->select(
                            'add_agent_id',
                            DB::raw('SUM(total_amount) AS wallet_balance')
                        )
                        ->where('isactive', 1)
                        ->groupBy('add_agent_id'),
                    'wallet',
                    'wallet.add_agent_id',
                    '=',
                    'add_agent.add_agent_id'
                )
                ->select('add_agent.*')
                ->orderByRaw('COALESCE(wallet.wallet_balance, 0) ASC');

            } elseif ($request->amount == 'high_low') {
                $query->leftJoinSub(
                    DB::table('agent_wallets')
                        ->select(
                            'add_agent_id',
                            DB::raw('SUM(total_amount) AS wallet_balance')
                        )
                        ->where('isactive', 1)
                        ->groupBy('add_agent_id'),
                    'wallet',
                    'wallet.add_agent_id',
                    '=',
                    'add_agent.add_agent_id'
                )
                ->select('add_agent.*')
                ->orderByRaw('COALESCE(wallet.wallet_balance, 0) DESC');

            } else {
                $query->orderBy('add_agent_id', 'DESC');
            }
        } else {
            $query->orderBy('add_agent_id', 'DESC');
        }
        // Countries
        $countries = AddAgent::whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');
        //Agents
        $agents = $query->paginate(20)->withQueryString();
        //Summary
        $total_agents = AddAgent::count();
        $active_agents = AddAgent::where('isactive', 1)->count();
        $pending_agents = AddAgent::where('isactive', '>', 1)->count();
        $inactive_agents = AddAgent::where('isactive', 0)->count();
        //Wallet
        $wallets = DB::table('agent_wallets')
            ->select(
                'add_agent_id',
                DB::raw('COALESCE(SUM(total_amount), 0) AS balance'),
                DB::raw('COALESCE(SUM(deposit_amount), 0) AS deposit'),
                DB::raw('COALESCE(SUM(withdrawal_amount), 0) AS withdrawal')
            )
            ->where('isactive', 1)
            ->groupBy('add_agent_id')
            ->get()
            ->keyBy('add_agent_id');
        // Bookings
        $bookings = DB::table('orders')
            ->select(
                'userID',
                DB::raw('COUNT(*) AS total')
            )
            ->where('userRole', 'agent')
            ->groupBy('userID')
            ->pluck('total', 'userID');
        return view('admin.agents.index', compact('total_agents','active_agents','pending_agents','inactive_agents','agents','wallets','bookings','countries'));
    }

    //Function for deactivate agent
    public function agent_status(Request $request) {
        //Get ids
        $ids = $request->ids;

        if (!$ids || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No agents selected.'
            ]);
        }
        $agents = DB::table('add_agent')->whereIn('add_agent_id', $ids)->get();
        foreach ($agents as $agent) {
            if ($request->action === 'activate') {
                DB::table('add_agent')
                    ->where('add_agent_id', $agent->add_agent_id)
                    ->update([
                        'isactive' => 1,
                        'timestamp' => now(),
                    ]);
                //Send email
                if (!empty($agent->email)) {
                    Mail::to($agent->email)->send(
                        new AgentActivatedMail($agent)
                    );
                }
            } else {
                DB::table('add_agent')
                    ->where('add_agent_id', $agent->add_agent_id)
                    ->update([
                        'isactive' => 0,
                        'timestamp' => now(),
                    ]);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Agent status updated successfully.'
        ]);
    }

    //Function for export report
    public function agent_export() {
        //Get agents
        $agents = AddAgent::orderBy('add_agent_id', 'DESC')->get();
        //Wallet
        $wallets = DB::table('agent_wallets')
            ->select(
                'add_agent_id',
                DB::raw('COALESCE(SUM(total_amount), 0) AS balance'),
                DB::raw('COALESCE(SUM(deposit_amount), 0) AS deposit'),
                DB::raw('COALESCE(SUM(withdrawal_amount), 0) AS withdrawal')
            )
            ->where('isactive', 1)
            ->groupBy('add_agent_id')
            ->get()
            ->keyBy('add_agent_id');
        //Bookings
        $bookings = DB::table('orders')
            ->select(
                'userID',
                DB::raw('COUNT(*) AS total')
            )
            ->where('userRole', 'agent')
            ->groupBy('userID')
            ->pluck('total', 'userID');

        $rows = [];
        foreach ($agents as $agent) {
        if ((int) $agent->isactive === 1) {
                $status = 'Active';
            } elseif ((int) $agent->isactive === 0) {
                $status = 'Inactive';
            } else {
                $status = 'Pending';
            }
            $rows[] = [
                $agent->referral_code,
                $agent->use_referral_code,
                $agent->name,
                'AGT-' . $agent->add_agent_id,
                $agent->contact_person,
                $agent->email,
                $agent->country ?? 'N/A',
                '$' . number_format($wallets[$agent->add_agent_id]->balance ?? 0, 2),
                number_format($bookings[$agent->add_agent_id] ?? 0),
                $status,
                $agent->date ?? 'N/A',
            ];
        }
        return ExportHelper::download(
            'all-agent-report.xlsx',
            'ALL AGENT REPORT',['Referral Code','Use Referral Code','Company','Agent ID','Contact Person','Email','Country','Wallet Balance','Total Bookings','Status','Registered Date'],
            $rows
        );
    }
    
    //Function for agent wallet
    public function agent_wallet(Request $request, $add_agent_id) {
        //Get agents
        $agent = AddAgent::findOrFail($add_agent_id);
        //Query
        $query = AgentWallet::where('add_agent_id', $add_agent_id)->where('isactive', 1);
        //Search
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(trasaction_number) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(account_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(payment_mathod) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(commets) LIKE ?', ["%{$search}%"])
                    ->orWhere('order_id', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%")
                    ->orWhereRaw('LOWER(payment_option) LIKE ?', ["%{$search}%"]);
            });
        }
        if ($request->filled('from_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('deposit_date', '>=', $request->from_date)
                    ->orWhereDate('withdrawal_date', '>=', $request->from_date);
            });
        }
        if ($request->filled('to_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('deposit_date', '<=', $request->to_date)
                    ->orWhereDate('withdrawal_date', '<=', $request->to_date);
            });
        }
        if ($request->type === 'credit') {
            $query->where('deposit_amount', '>', 0);
        } elseif ($request->type === 'debit') {
            $query->where('withdrawal_amount', '<', 0);
        }

        $all = $query->orderBy('id', 'DESC')->get();

        //Current Wallet Totals
        $walletQuery = AgentWallet::where('add_agent_id', $add_agent_id)
            ->where('isactive', 1)
            ->get();

        $totalCredit = $walletQuery->sum(function ($transaction) {
            return (float) ($transaction->deposit_amount ?? 0);
        });

        $totalUsed = $walletQuery->sum(function ($transaction) {
            return abs((float) ($transaction->withdrawal_amount ?? 0));
        });

        $balance = $totalCredit - $totalUsed;
        $runningBalance = $balance;

        $all = $all->map(function ($transaction) use (&$runningBalance) {
            $deposit = (float) ($transaction->deposit_amount ?? 0);
            $withdrawal = abs(
                (float) ($transaction->withdrawal_amount ?? 0)
            );
            $cancelAmount = (float) (
                $transaction->cancel_amount ?? 0
            );

            $isRefund =
                strtolower(trim($transaction->payment_option ?? '')) === 'refund'
                ||
                strtolower(trim($transaction->payment_status ?? '')) === 'refund';

            if ($isRefund) {
                $type = 'refund';
                $displayCredit = $deposit;
                $displayDebit = 0;
                $transactionDate = $transaction->deposit_date;
                $balanceAtTransaction = $runningBalance;
                $runningBalance -= $displayCredit;
            }  elseif ($deposit > 0) {
                $type = 'credit';
                $displayCredit = $deposit;
                $displayDebit = 0;
                $transactionDate = $transaction->deposit_date;
                $balanceAtTransaction = $runningBalance;
                $runningBalance -= $displayCredit;

            } else {
                $type = 'debit';
                $displayCredit = 0;
                $displayDebit = $withdrawal;
                $transactionDate = $transaction->withdrawal_date;
                $balanceAtTransaction = $runningBalance;
                $runningBalance += $displayDebit;
            }
            $transaction->display_credit = $displayCredit;
            $transaction->display_debit = $displayDebit;
            $transaction->display_type = $type;
            $transaction->display_balance = $balanceAtTransaction;
            $transaction->display_date = $transactionDate;
            return $transaction;
        });
        $page = (int) $request->get('page', 1);
        $perPage = 20;
        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $all->forPage($page, $perPage),
            $all->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );
        return view('admin.agents.agent-wallet', compact('agent','transactions','totalCredit','totalUsed','balance'));
    }

    //Funcion for create agent wallet popup
    public function agent_wallet_topup(Request $request) {
        //Create agent wallets
          $walletId = DB::table('agent_wallets')->insertGetId([
            'add_agent_id' => $request->add_agent_id,
            'deposit_date' => now('Asia/Kolkata'),
            'deposit_amount' => $request->amount,
            'total_amount' => $request->amount,
            'account_name' => $request->payment_method,
            'payment_mathod' => $request->payment_method,
            'trasaction_number' => $request->payment_reference,
            'commets' => $request->remarks,
            'payment_option' => 'B2B Top up - Admin Panel',
            'payment_status' => 'paid',
            'isactive' => 1,
        ]);
        //Response
        return response()->json([
            'status' => true,
            'redirect_url' => route(
                'admin.agent.wallet.invoice',
                $walletId
            )
        ]);
    }

    //Function for agent wallent invoice
    public function agent_wallet_invoice($wallet_id) {
        //Get wallet transaction
        $transaction = AgentWallet::where('id', $wallet_id)->where('isactive', 1)->firstOrFail();
        //Get agent
        $agent = AddAgent::where('add_agent_id', $transaction->add_agent_id)->firstOrFail();
        //Credit / Debit amount
        $credit = (float) ($transaction->deposit_amount ?? 0);
        $debit = abs((float) ($transaction->withdrawal_amount ?? 0));
        if ($credit > 0) {
            $type = 'credit';
            $amount = $credit;
            $transactionDate = $transaction->deposit_date;
        } else {
            $type = 'debit';
            $amount = $debit;
            $transactionDate = $transaction->withdrawal_date;
        }
        $previousBalance = DB::table('agent_wallets')
            ->where('add_agent_id', $transaction->add_agent_id)
            ->where('isactive', 1)
            ->where('id', '<', $transaction->id)
            ->selectRaw('
                COALESCE(SUM(deposit_amount), 0)
                +
                COALESCE(SUM(withdrawal_amount), 0)
                AS balance
            ')
            ->value('balance');
        $previousBalance = (float) ($previousBalance ?? 0);
        if ($type === 'credit') {
            $newBalance = $previousBalance + $amount;
        } else {
            $newBalance = $previousBalance - $amount;
        }
        $paymentStatus = $transaction->payment_status ? ucfirst($transaction->payment_status) : 'Completed';
        $invoiceDate = $transactionDate ? Carbon::parse($transactionDate)->format('d F Y') : 'N/A';
        $paymentDate = $transactionDate ? Carbon::parse($transactionDate)->format('d F Y') : 'N/A';
        $paymentTime = $transactionDate ? Carbon::parse($transactionDate)->format('h:i A') : 'N/A';
        //Invoice data
        $invoice = [
            'invoice_no' => 'SLW-WAL-' . $transaction->id,
            'transaction_id' => $transaction->trasaction_number ?? 'TXN-' . $transaction->id,
            'invoice_date' => $invoiceDate,
            'payment_date' => $paymentDate,
            'payment_time' => $paymentTime,
            'payment_mathod' => $transaction->payment_mathod ?? $transaction->account_name ?? 'N/A',
            'payment_reference' => $transaction->trasaction_number ?? 'N/A',
            'payment_status' => $paymentStatus,
            'currency' => 'USD',
            'amount' => $amount,
            'type' => $type,
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
            'amount_words' => '',
            'commets' => $transaction->commets ?? '',
            'agent_id' => 'AGT-' . $agent->add_agent_id,
            'company' => $agent->name ?? 'N/A',
            'contact_person' => $agent->contact_person ?? 'N/A',
            'email' => $agent->email ?? 'N/A',
            'mobile' => $agent->telephone ?? 'N/A',
            'address' => $agent->address ?? 'N/A',
            'created_by' => auth()->user()->adm_login_id ?? 'Admin',
        ];
        return view('admin.agents.topup-invoice',compact('invoice','transaction','agent'));
    }

    //Function for agent bulk email
    public function agent_bulk_email() {
        //Get agents
        $agents = AddAgent::orderBy('add_agent_id', 'DESC')->get();
        $totalAgents = AddAgent::count();
        $activeAgents = AddAgent::where('isactive', 1)->count();
        //Get courties
        $countries = AddAgent::whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        return view('admin.agents.agent-bulk-email', compact('agents','totalAgents','activeAgents','countries'));
    }

    //Function for Send bulk email
    public function send_bulk_email(Request $request) {
        //Validate input fields
        $request->validate([
            'agents' => 'required|array|min:1',
            'agents.*' => 'integer',
            'sender_name' => 'required|string|max:100',
            'reply_to' => 'required|email',
            'subject' => 'required|string|max:150',
            'body' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);
        //Get agent
        $query = AddAgent::whereIn('add_agent_id', $request->agents);
        if ($request->boolean('only_active')) {
            $query->where('isactive', 1);
        }

        $agents = $query->get();
        if ($agents->isEmpty()) {
            return redirect()->back()->with('error', 'Please select at least one agent.');
        }

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->getRealPath();
            $attachmentName = $request->file('attachment')->getClientOriginalName();
            $attachmentMime = $request->file('attachment')->getMimeType();
        }

        $sentCount = 0;
        foreach ($agents as $agent) {
            if (empty($agent->email)) {
                continue;
            }
            $body = str_replace(
                ['{agent_name}', '{company_name}'],
                [
                    $agent->contact_person ?? $agent->name ?? 'Agent',
                    $agent->name ?? 'Sun Leisure World'
                ],
                $request->body
            );

            $body = preg_replace('/^\s*Dear\s+[^\r\n,]+,?\s*/i', '', $body);
            Mail::to($agent->email)->send(
                new AgentBulkEmailMail(
                    $agent,
                    $request->subject,
                    $body,
                    $request->sender_name,
                    $request->reply_to,
                    $attachmentPath,
                    $attachmentName,
                    $attachmentMime
                )
            );
            $sentCount++;
        }
        if ($sentCount === 0) {
            return redirect()->back()->with('unsuccess', 'No selected agent has a valid email address.');
        }
        return redirect()->back()->with('success', $sentCount . ' email(s) sent successfully.');
    }

    //Function for refund agent wallet
    public function refund_agent_wallet(Request $request) {
        //Validate input fileds
        $request->validate([
            'wallet_id'     => 'required|integer|exists:agent_wallets,id',
            'refund_amount' => 'required|numeric|min:0.01',
            'remarks'       => 'nullable|string|max:1000',
        ]);
        try {
            $transaction = DB::table('agent_wallets')
                ->where('id', $request->wallet_id)
                ->where('isactive', 1)
                ->where('withdrawal_amount', '<', 0)
                ->first();
            if (!$transaction) {
                return redirect()->back()->with('unsuccess', 'Debit transaction not found.');
            }
            //Original debit amount
            $originalDebit = abs(
                (float) ($transaction->withdrawal_amount ?? 0)
            );
            // Already refunded amount
            $alreadyRefunded = (float) (
                $transaction->cancel_amount ?? 0
            );
            //Remaining refundable amount
            $remainingRefund = $originalDebit - $alreadyRefunded;
            if ($remainingRefund <= 0) {
                return redirect()->back()->with('unsuccess','This payment is already fully refunded.');
            }
            $refundAmount = (float) $request->refund_amount;
            if ($refundAmount > $remainingRefund) {
                return redirect()->back()->with('unsuccess', 'Refund amount cannot be greater than $' .number_format($remainingRefund, 2));
            }
            $newDepositAmount =
                (float) ($transaction->deposit_amount ?? 0)
                + $refundAmount;
            $newTotalAmount =
                (float) ($transaction->total_amount ?? 0)
                + $refundAmount;
            $newCancelAmount =
                $alreadyRefunded
                + $refundAmount;
            $updated = DB::table('agent_wallets')
                ->where('id', $transaction->id)
                ->update([
                    'deposit_amount' => $newDepositAmount,
                    'total_amount' => $newTotalAmount,
                    'cancel_amount' => $newCancelAmount,
                    'deposit_date' => now(),
                    'payment_option' => 'Refund',
                    'commets' => $request->remarks,
                ]);
            if ($updated === 0) {
                return redirect()->back()->with('unsuccess', 'No changes were made to this transaction.');
            }
            return redirect()->back()->with('success', 'Refund processed successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error','Refund failed: ' . $e->getMessage());
        }
    }
}