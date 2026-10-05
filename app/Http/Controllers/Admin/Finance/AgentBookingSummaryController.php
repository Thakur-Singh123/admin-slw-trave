<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Helpers\BookingPriceHelper;
use App\Http\Controllers\Controller;
use App\Models\AddAgent;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentBookingSummaryController extends Controller
{
    //Function for agent booking summary
    public function index(Request $request) {
        //Get dates
        $fromDate = $request->filled('from') ? $request->from : now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->filled('to') ? $request->to : now()->format('Y-m-d');
        //Get orders
        $orders = Order::query()
            ->where('userRole', 'agent')
            ->whereNotNull('userID')
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate)
            ->select([
                'id',
                'userID',
                'type',
                'item_id',
                'option_id',
                'item_name',
                'start_date',
                'qty_adult',
                'qty_child',
                'total_amount',
                'currency',
                'date',
            ])
            ->orderByDesc('id')
            ->get();
        $agentOrders = $orders->groupBy('userID');
        $agentIds = $agentOrders->keys()->filter()->values();
        //Get agents
        $agentData = AddAgent::whereIn(
            'add_agent_id',
            $agentIds
        )->get();
        $wallets = DB::table('agent_wallets')
            ->select(
                'add_agent_id',
                DB::raw('COALESCE(SUM(total_amount),0) as balance')
            )
            ->where('isactive', 1)
            ->whereIn('add_agent_id', $agentIds)
            ->groupBy('add_agent_id')
            ->get()
            ->keyBy('add_agent_id');
        $agents = $agentData->map(function ($agent) use (
            $agentOrders,
            $wallets
        ) {
            $orders = $agentOrders->get(
                $agent->add_agent_id,
                collect()
            );
            if ($orders->isEmpty()) {
                return null;
            }
            $net = 0;
            $sell = 0;
            $lastBooking = null;
            foreach ($orders as $order) {
                $price = BookingPriceHelper::calculate($order);
                $net += (float) ($price['net_amount'] ?? 0);
                $sell += (float) ($price['sell_amount'] ?? 0);
                if (!$lastBooking || $order->date > $lastBooking) {
                    $lastBooking = $order->date;
                }
            }
            return [
                'id' => $agent->add_agent_id,
                'code' => 'AGT-' . $agent->add_agent_id,
                'company' => $agent->name ?? 'N/A',
                'name' => $agent->name ?? 'N/A',
                'email' => $agent->email ?? 'N/A',
                'phone' => $agent->phone ?? 'N/A',
                'country' => $agent->country ?? 'N/A',
                'bookings' => $orders->count(),
                'net' => round($net, 2),
                'sell' => round($sell, 2),
                'wallet' => round(
                    (float) ($wallets->get($agent->add_agent_id)->balance ?? 0),
                    2
                ),
                'last_booking' => $lastBooking,
                'status' => (int) ($agent->isactive ?? 0) === 1
                    ? 'Active'
                    : 'Inactive',
            ];
        })->filter()->values();
        //
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $agents = $agents->filter(function ($agent) use ($search) {
                return
                    str_contains(strtolower($agent['company']), $search) ||
                    str_contains(strtolower($agent['name']), $search) ||
                    str_contains(strtolower($agent['email']), $search) ||
                    str_contains(strtolower($agent['code']), $search) ||
                    str_contains(strtolower($agent['phone']), $search);
            })->values();
        }
        //Country
        if ($request->filled('country')) {
            $country = strtolower(trim($request->country));
            $agents = $agents->filter(
                fn ($agent) =>
                    strtolower(trim($agent['country'])) === $country
            )->values();
        }
        //Status
        if ($request->filled('status')) {
            $status = strtolower(trim($request->status));
            $agents = $agents->filter(
                fn ($agent) =>
                    strtolower(trim($agent['status'])) === $status
            )->values();
        }
        //Total
        $totalAgentCount = $agents->count();
        $totalBookings = $agents->sum('bookings');
        $totalNet = $agents->sum('net');
        $totalSell = $agents->sum('sell');
        $totalWallet = $agents->sum('wallet');
        $totalMargin = $totalSell - $totalNet;
        //Sorting
        $sortBy = $request->get('sort_by', 'bookings');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowed = [
            'company',
            'country',
            'bookings',
            'net',
            'sell',
            'margin',
            'wallet',
            'last_booking',
            'status',
        ];
        if (!in_array($sortBy, $allowed)) {
            $sortBy = 'bookings';
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        $agents = $agents->sort(function ($a, $b) use (
            $sortBy,
            $sortOrder
        ) {
            $aValue = $sortBy === 'margin'
                ? $a['sell'] - $a['net']
                : $a[$sortBy];
            $bValue = $sortBy === 'margin'
                ? $b['sell'] - $b['net']
                : $b[$sortBy];
            if ($sortBy === 'last_booking') {
                $aValue = $aValue ? strtotime($aValue) : 0;
                $bValue = $bValue ? strtotime($bValue) : 0;
            }
            if (is_string($aValue)) {
                $aValue = strtolower(trim($aValue));
                $bValue = strtolower(trim($bValue));
            }
            $result = $aValue == $bValue ? 0 : ($aValue < $bValue ? -1 : 1);
            return $sortOrder === 'asc' ? $result : -$result;
        })->values();
        //Countries
        $countries = AddAgent::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');
        return view('admin.finances.agent-summaries.agent-booking-summary', compact('agents','fromDate','toDate','countries','totalAgentCount','totalBookings','totalNet','totalSell','totalWallet','totalMargin'));
    }

    //Function for agent booking settlement detail
    public function agent_booking_settlement(Request $request) {
        //Get agent id
        $agentId = (int) $request->agent_id;
        $fromDate = $request->filled('from') ? $request->from : now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->filled('to') ? $request->to : now()->format('Y-m-d');
        //Get agent
        $agent = AddAgent::where('add_agent_id', $agentId)->firstOrFail();
        //Get orders
        $orders = Order::query()
            ->where('userRole', 'agent')
            ->where('userID', $agentId)
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate)
            ->select([
                'id',
                'type',
                'item_id',
                'option_id',
                'item_name',
                'start_date',
                'qty_adult',
                'qty_child',
                'total_amount',
                'currency',
                'date',
                'booking_reference',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        $totalNet = 0;
        $totalSell = 0;
        $totalAdult = 0;
        $totalChild = 0;

        $bookings = $orders->map(function ($order) use (
            &$totalNet,
            &$totalSell,
            &$totalAdult,
            &$totalChild
        ) {
            $price = BookingPriceHelper::calculate($order);

            $net = (float) ($price['net_amount'] ?? 0);
            $sell = (float) ($price['sell_amount'] ?? 0);
            $discount = (float) ($price['discount'] ?? 0);

            $adult = (int) ($order->qty_adult ?? 0);
            $child = (int) ($order->qty_child ?? 0);

            $totalNet += $net;
            $totalSell += $sell;
            $totalAdult += $adult;
            $totalChild += $child;

            return [
                'order_id' => $order->booking_reference ?: 'SLW-' . $order->id,
                'type' => ucfirst(strtolower(trim($order->type ?? 'Booking'))),
                'name' => $order->item_name ?: 'Booking',
                'booking_date' => $order->date,
                'travel_date' => $order->start_date,
                'net' => round($net, 2),
                'sell' => round($sell, 2),
                'discount' => round($discount, 2),
                'adult' => $adult,
                'child' => $child,
                'status' => $order->status ?: 'Pending',
            ];
        });

        $wallet = DB::table('agent_wallets')->where('add_agent_id', $agentId)->where('isactive', 1)->sum('total_amount');

        $totalMargin = $totalSell - $totalNet;
        $totalPax = $totalAdult + $totalChild;

        $statementNo = 'AGT-ST-' . now()->format('Ymd') . '-' . $agentId;

        return view('admin.finances.agent-summaries.agent-booking-settlement', compact('agent','bookings','fromDate','toDate','wallet','totalNet','totalSell','totalMargin','totalAdult','totalChild','totalPax','statementNo'));
    }
}