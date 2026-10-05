<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AgentSubscription;
use App\Models\AgentLeadSubscriptionPayment;
use App\Models\AddAgent;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Enquiry;

class AgentSubscriptionController extends Controller
{
    //Function for all subscription List
    public function index(Request $request) {
        //Get today record
        $today = Carbon::today();
        //Get agent sub payments
        $records = AgentLeadSubscriptionPayment::with(['subscription', 'agent'])
            ->whereRaw('LOWER(status) = ?', ['paid'])
            ->latest('id')
            ->get();

        $getSub = fn($payment) => $payment->subscription ?: AgentSubscription::where('agent_id', $payment->agent_id)
            ->where('status', 1)
            ->latest('id')
            ->first();

        $subscriptions = $records->map(function ($payment) use ($today, $getSub) {
            $sub = $getSub($payment);

            if (!$sub) return null;

            $agent = $payment->agent;
            $expiry = $sub->end_date ? Carbon::parse($sub->end_date) : null;
            $daysLeft = $expiry ? $today->diffInDays($expiry, false) : 0;

            return [
                'id' => 'SUB-' . $sub->id,
                'company' => $agent->name ?? 'N/A',
                'agent_id' => 'AGT-' . $payment->agent_id,
                'email' => $agent->email ?? 'N/A',
                'plan' => $sub->plan_type ?? 'N/A',
                'billing' => 'Monthly',
                'start_date' => $sub->start_date,
                'expiry_date' => $sub->end_date,
                'days_left' => $daysLeft,
                'amount' => ($payment->amount ?? 0) / 100,
                'currency' => $payment->currency ?? 'USD',
                'payment' => 'Paid',
                'payment_status' => 'Paid',
                'payment_id' => $payment->id,
                'subscription_id' => $sub->id,
                'status' => $expiry && $expiry->lt($today)
                    ? 'Expired'
                    : ($daysLeft <= 15 ? 'Expiring' : 'Active'),
            ];
        })->filter()->values();
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $subscriptions = $subscriptions->filter(fn($row) =>
                str_contains(strtolower($row['company']), $search) ||
                str_contains(strtolower($row['email']), $search) ||
                str_contains(strtolower($row['agent_id']), $search) ||
                str_contains(strtolower($row['id']), $search)
            )->values();
        }
        if ($request->filled('plan') && $request->plan != 'all') {
            $subscriptions = $subscriptions
                ->where('plan', $request->plan)
                ->values();
        }
        if ($request->filled('status') && $request->status != 'all') {
            $status = strtolower($request->status);

            $subscriptions = $subscriptions->filter(
                fn($row) => strtolower($row['status']) == $status
            )->values();
        }
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $from = $request->from_date ?: $request->to_date;
            $to = $request->to_date ?: $request->from_date;
            $subscriptions = $subscriptions->filter(function ($row) use ($from, $to) {
                if (empty($row['start_date']) || empty($row['expiry_date'])) {
                    return false;
                }
                $start = Carbon::parse($row['start_date'])->format('Y-m-d');
                $end = Carbon::parse($row['expiry_date'])->format('Y-m-d');
                return $start <= $to && $end >= $from;
            })->values();
        }
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 20;
        $paginator = new LengthAwarePaginator(
            $subscriptions->forPage($page, $perPage)->values(),
            $subscriptions->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );
        $subscriptions = collect($paginator->items());
        $all = $records->pluck('agent_id')->unique();
        $activeSubscribers = $records->filter(function ($payment) use ($today, $getSub) {
            $sub = $getSub($payment);
            return $sub && $sub->end_date &&
                Carbon::parse($sub->end_date)->gte($today);
        })->pluck('agent_id')->unique()->count();
        $expiringSubscribers = $records->filter(function ($payment) use ($today, $getSub) {
            $sub = $getSub($payment);
            if (!$sub || !$sub->end_date) return false;
            $days = $today->diffInDays(
                Carbon::parse($sub->end_date),
                false
            );
            return $days >= 0 && $days <= 15;
        })->pluck('agent_id')->unique()->count();
        $expiredSubscribers = $records->filter(function ($payment) use ($today, $getSub) {
            $sub = $getSub($payment);
            return $sub && $sub->end_date &&
                Carbon::parse($sub->end_date)->lt($today);
        })->pluck('agent_id')->unique()->count();
        $totalSubscribers = $all->count();
        //Get leads
        $agentLeadCounts = Enquiry::selectRaw(
                'lead_assigned_agent_id, COUNT(*) as total'
            )
            ->whereNotNull('lead_assigned_agent_id')
            ->groupBy('lead_assigned_agent_id')
            ->pluck('total', 'lead_assigned_agent_id');
        $agents = AddAgent::where('isactive', 1)->orderBy('name')->get();

        return view('admin.agents.agent-subscribers', compact('subscriptions','paginator','agents','totalSubscribers','activeSubscribers','expiringSubscribers','expiredSubscribers','agentLeadCounts'));
    }
    
    //Function for store subscription
    public function store(Request $request) {
        //Validate input fileds
        $request->validate([
            'agent_id' => 'required|integer|exists:add_agent,add_agent_id',
            'plan' => 'required',
            'billing' => 'required|in:Monthly,Yearly',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:start_date',
            'amount' => 'required|numeric|min:1',
            'payment_status' => 'required|in:Paid,Pending,Failed',
            'payment_reference' => 'required|string|max:255|unique:agent_lead_subscription_payments,receipt',
        ]);
        DB::beginTransaction();
        try {
            $paymentStatus = strtolower($request->payment_status);
            //Create sub
            $subscription = AgentSubscription::create([
                'subscription_amt' => $request->amount,
                'currency' => 'USD',
                'start_date' => $request->start_date,
                'end_date' => $request->expiry_date,
                'plan_type' => $request->plan,
                'agent_id' => $request->agent_id,
                'trasaction_id' => $request->payment_reference,
                'payment_option' => $request->payment_status,
                'status' => $paymentStatus == 'paid' ? 1 : 0,
                'lead_access' => 0,
            ]);
            //Create sub payment
            AgentLeadSubscriptionPayment::create([
                'agent_id' => $request->agent_id,
                'subscription_id' => $subscription->id,
                'receipt' => $request->payment_reference,
                'razorpay_order_id' => $request->payment_reference,
                'razorpay_payment_id' => 'MANUAL-PAY-' . $subscription->id,
                'amount' => (int) ($request->amount * 100),
                'currency' => 'USD',
                'status' => $paymentStatus,
                'paid_at' => $paymentStatus == 'paid' ? now() : null,
            ]);
            DB::commit();

            return redirect()->route('admin.agent.subscriptions')->with('success', 'Subscription added successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('unsuccess', $e->getMessage());
        }
    }
}