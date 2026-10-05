<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AddAgent;
use App\Models\AddSupplier;
use App\Models\Order;
use Carbon\Carbon;
use App\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //Function for admin dashboard
    public function dashboard() {
        //Get agents
        $total_agents = AddAgent::count();
        $last_month = AddAgent::where('isactive', 1)
            ->whereBetween('date', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->count();
        $agent_percentage = $last_month
            ? (($total_agents - $last_month) / $last_month) * 100
            : 0;

        //Get suppliers
        $total_suppliers = AddSupplier::count();
        $pending_suppliers = AddSupplier::where('isactive', 0)->count();

        //Get revenue
        $currency_totals = Order::where('payment_status', 'paid')
            ->selectRaw('currency, SUM(total_amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency');

        $total_revenue = 0;

        foreach ($currency_totals as $currency => $amount) {
        $total_revenue += CurrencyHelper::toUsd($amount, $currency);
        }

        $total_revenue = round($total_revenue, 2);

        $current_month_revenue = Order::whereMonth('paid_payment_date', now()->month)->whereYear('paid_payment_date', now()->year)->sum('total_amount');
        $last_month_revenue = Order::whereMonth('paid_payment_date', now()->subMonth()->month)->whereYear('paid_payment_date', now()->subMonth()->year)->sum('total_amount');
        $revenue_percentage = $last_month_revenue > 0 ? (($current_month_revenue - $last_month_revenue) / $last_month_revenue) * 100 : 0;
        
        //Get orders
        $total_bookings = Order::count();
        $thisMonth = Order::whereMonth('paid_payment_date', now()->month)->whereYear('paid_payment_date', now()->year)->count();
        $lastMonth = Order::whereMonth('paid_payment_date', now()->subMonth()->month)->whereYear('paid_payment_date', now()->subMonth()->year)->count();
        $percentage = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;
        
        //Latest Booking
        $latest_bookings = Order::OrderBy('ID', 'DESC')->take(5)->get();

        //Business Snapshot
        $today = now()->toDateString();
        //Today's bookings
        $today_bookings = Order::whereDate('date', $today)->count();

        $yesterday_bookings = Order::whereDate('paid_payment_date', today()->subDay())->count();
        $booking_percentage = $yesterday_bookings ? round(($today_bookings - $yesterday_bookings) / $yesterday_bookings * 100, 1): 0;

        //Pending requests
        $pending_requests = Order::where('status', 'pending')->count();

        $today_agent_topups = DB::table('agent_wallets')
            ->whereDate('deposit_date', today())
            ->sum('deposit_amount');

        // $today_agent_count = DB::table('agent_wallets')
        //     ->whereDate('deposit_date', today())
        //     ->whereNotNull('add_agent_id')
        //     ->distinct('add_agent_id')
        //     ->count('add_agent_id');

        $today_agent_count = DB::table('agent_wallets')
            ->whereDate('deposit_date', today())
            ->whereNotNull('add_agent_id')
            ->count('add_agent_id');
            
        $yesterday = DB::table('agent_wallets')->whereDate('deposit_date', today()->subDay())->sum('deposit_amount');
        $topup_percentage = $yesterday ? round(($today_agent_topups - $yesterday) / $yesterday * 100, 1): 0;

        return view('admin.dashboard.index', compact('today_agent_count','total_agents','agent_percentage','total_suppliers','pending_suppliers','total_bookings','percentage','latest_bookings','total_revenue','revenue_percentage','today_bookings','booking_percentage','pending_requests','today_agent_topups','topup_percentage'));
    }
}
