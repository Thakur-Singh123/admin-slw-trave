<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\AddAgent;

class BookingController extends Controller
{
    //Function for all bookings
    public function index(Request $request) {
        //Get orders
        $query = Order::query();
        //Type
        if (
            $request->filled('type') &&
            $request->type != 'all'
        ) {
            $query->whereRaw(
                'TRIM(LOWER(type)) = ?',
                [strtolower(trim($request->type))]
            );
        }
        //Booking date
        // if ($request->filled('booking_from')) {
        //     $query->whereDate(
        //         'date',
        //         '>=',
        //         $request->booking_from
        //     );
        // }
        // if ($request->filled('booking_to')) {
        //     $query->whereDate(
        //         'date',
        //         '<=',
        //         $request->booking_to
        //     );
        // }
        //Booking date
        if ($request->filled('booking_from')) {
            $query->whereDate(
                'date',
                '>=',
                $request->booking_from
            );
        } else {
            $query->whereDate('date', today());
        }

        if ($request->filled('booking_to')) {
            $query->whereDate(
                'date',
                '<=',
                $request->booking_to
            );
        }
        //Travel date
        if ($request->filled('travel_from')) {
            $query->whereDate(
                'start_date',
                '>=',
                $request->travel_from
            );
        }
        if ($request->filled('travel_to')) {
            $query->whereDate(
                'start_date',
                '<=',
                $request->travel_to
            );
        }
        //Status
        if (
            $request->filled('status') &&
            $request->status != 'all'
        ) {
            $status = strtolower(
                trim($request->status)
            );
            if ($status == 'confirmed') {
                $query->whereRaw(
                    "TRIM(LOWER(status)) IN ('confirmed','confirm')"
                );
            } elseif ($status == 'pending') {
                $query->whereRaw(
                    "TRIM(LOWER(status)) = 'pending'"
                );
            } elseif ($status == 'on-hold') {
                $query->whereRaw(
                    "TRIM(LOWER(status)) IN ('hold','on hold','on-hold')"
                );
            } elseif ($status == 'cancelled') {
                $query->whereRaw(
                    "TRIM(LOWER(status)) IN ('cancel','cancelled','canceled')"
                );
            }
        }
        //Source
        if (
            $request->filled('source') &&
            $request->source != 'all'
        ) {
            $query->whereRaw(
                'TRIM(LOWER(source)) = ?',
                [strtolower(trim($request->source))]
            );
        }
        //Summary
        $totalBookings = Order::count();
        $confirmedBookings = Order::whereRaw("TRIM(LOWER(status)) IN ('confirmed','confirm')")->count();
        $pendingBookings = Order::whereRaw("TRIM(LOWER(status)) IN ('pending','hold','on hold','on-hold')")->count();
        $cancelledBookings = Order::whereRaw("TRIM(LOWER(status)) IN ('cancel','cancelled','canceled')")->count();
        //Type counts
        $typeCounts = [
            'all' => Order::count(),
            'tour' => Order::whereRaw("TRIM(LOWER(type)) = 'tour'")->count(),
            'transfer' => Order::whereRaw("TRIM(LOWER(type)) = 'transfer'")->count(),
            'hotel' => Order::whereRaw("TRIM(LOWER(type)) = 'hotel'")->count(),
            'ferry' => Order::whereRaw("TRIM(LOWER(type)) = 'ferry'")->count(),
            'package' => Order::whereRaw("TRIM(LOWER(type)) = 'package'")->count(),
        ];
        //Dynamic sources
        $sources = Order::whereNotNull('source')
            ->where('source', '!=', '')
            ->select('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');
        //Pagination
        $length = (int) $request->get('length', 20);
        if (!in_array($length, [10, 20, 25, 50, 100])) {
            $length = 20;
        }
        $bookings = $query
            ->select([
                'id',
                'type',
                'start_date',
                'date',
                'qty_adult',
                'qty_child',
                'total_amount',
                'userID',
                'userRole',
                'item_name',
                'first_name',
                'last_name',
                'email',
                'phone_country_code',
                'phone',
                'from',
                'to',
                'HotelName',
                'HotelAddress',
                'TransferFrom',
                'TransferFromAddress',
                'status',
                'currency',
                'booking_reference',
                'source',
                'payment_status',
            ])
            ->orderBy('id', 'DESC')
            ->paginate($length)
            ->withQueryString();
        //Agent names
        $agentIds = $bookings->getCollection()->where('userRole', 'agent')->pluck('userID')->filter()->unique();
        //Get agents
        $agents = AddAgent::whereIn('add_agent_id', $agentIds)->pluck('name', 'add_agent_id');
        
        return view('admin.finances.bookings.all-bookings', compact('bookings','agents','typeCounts','totalBookings','confirmedBookings','pendingBookings','cancelledBookings','sources'));
    }
}