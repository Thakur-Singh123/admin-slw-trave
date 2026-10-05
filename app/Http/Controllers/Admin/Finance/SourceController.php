<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Helpers\BookingPriceHelper;
use App\Helpers\ExportHelper;
use App\Models\Order;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    //Function for summary 
    public function index(Request $request) {
        //Get summary data
        $summaryData = Order::query()
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->select('source')
            ->selectRaw('COUNT(*) as bookings')
            ->selectRaw("
                SUM(
                    CASE
                        WHEN LOWER(TRIM(status)) IN (
                            'confirmed',
                            'confirm',
                            'success',
                            'successful',
                            'complete',
                            'completed'
                        )
                        THEN 1
                        ELSE 0
                    END
                ) as confirmed
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN LOWER(TRIM(status)) IN (
                            'cancel',
                            'cancelled',
                            'canceled'
                        )
                        THEN 1
                        ELSE 0
                    END
                ) as cancelled
            ")
            ->selectRaw(
                'COALESCE(SUM(total_amount), 0) as amount'
            )
            ->groupBy('source')
            ->get();
        $totalSources = $summaryData->count();
        $totalBookings = $summaryData->sum('bookings');
        $totalConfirmed = $summaryData->sum('confirmed');
        $totalCancelled = $summaryData->sum('cancelled');
        $totalAmount = $summaryData->sum('amount');

        $averageValue = $totalBookings > 0
            ? $totalAmount / $totalBookings
            : 0;
        //SOURCE TYPES
        $sourceTypes = Order::query()
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');
        //FILTER QUERY
        $query = Order::query()
            ->whereNotNull('source')
            ->where('source', '!=', '');
        //Search
        if ($request->filled('search')) {
            $search = trim(
                $request->search
            );
            $query->where(
                'source',
                'like',
                "%{$search}%"
            );
        }
        //From Date
        if ($request->filled('from_date')) {
            $query->whereDate(
                'date',
                '>=',
                $request->from_date
            );
        }
        //To Date
        if ($request->filled('to_date')) {
            $query->whereDate(
                'date',
                '<=',
                $request->to_date
            );
        }
        //Source Type
        if (
            $request->filled('source_type') &&
            $request->source_type !== 'all'
        ) {
            $query->where(
                'source',
                $request->source_type
            );
        }
        //SOURCE DATA
        $sourceData = $query
            ->select('source')
            ->selectRaw('COUNT(*) as bookings')
            ->selectRaw("
                SUM(
                    CASE
                        WHEN LOWER(TRIM(status)) IN (
                            'confirmed',
                            'confirm',
                            'success',
                            'successful',
                            'complete',
                            'completed'
                        )
                        THEN 1
                        ELSE 0
                    END
                ) as confirmed
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN LOWER(TRIM(status)) IN (
                            'cancel',
                            'cancelled',
                            'canceled'
                        )
                        THEN 1
                        ELSE 0
                    END
                ) as cancelled
            ")
            ->selectRaw(
                'COALESCE(SUM(total_amount), 0) as amount'
            )
            ->selectRaw(
                'MAX(date) as last_booking'
            )
            ->groupBy('source')
            ->get();
        $sources = $sourceData->map(
            function ($item) {
                $source = trim(
                    $item->source
                );
                return [
                    'name' => $source,
                    'type' => $source,
                    'bookings' => (int) $item->bookings,
                    'confirmed' => (int) $item->confirmed,
                    'cancelled' =>  (int) $item->cancelled,
                    'amount' => (float) $item->amount,
                    'last_booking' => $item->last_booking,
                ];
            }
        );
        //SORTING
        $sortBy = $request->get('sort_by','amount');
        $sortOrder = $request->get('sort_order', 'desc');
        $sortFields = [
            'name',
            'type',
            'bookings',
            'confirmed',
            'cancelled',
            'amount',
            'last_booking',
        ];
        if (!in_array(
            $sortBy,
            $sortFields
        )) {
            $sortBy = 'amount';
        }
        $sortOrder = $sortOrder === 'asc' ? 'asc' : 'desc';
        $sources = $sources
            ->sortBy(
                fn ($source) =>
                    $source[$sortBy] ?? '',
                SORT_REGULAR,
                $sortOrder === 'desc'
            )
            ->values();
        $maxAmount =
            $sources->max('amount') ?: 0;
        return view('admin.finances.soruces-summaries.source-summary', compact('sources','sourceTypes','totalSources','totalBookings','totalConfirmed','totalCancelled','totalAmount','averageValue','maxAmount'));
    }


    //Function for export source summary
    public function export(Request $request) {
        $rows = json_decode(
            $request->rows,
            true
        );
        if (
            !is_array($rows) ||
            empty($rows)
        ) {
            return back()->with(
                'error',
                'No records to export.'
            );
        }
        return ExportHelper::download(
            'source-summary-report.xlsx',
            'SOURCE SUMMARY REPORT',
            [
                'Source Name',
                'Source Type',
                'Total Bookings',
                'Confirmed',
                'Cancelled',
                'Total Amount',
                'Last Booking',
            ],
            $rows
        );
    }

    //Function for source booking details
    public function source_booking_details(Request $request) {
        //SOURCE
        $sourceName = trim($request->source);
        //DATES
        $fromDate = $request->filled('from') ? $request->from : null;
        $toDate = $request->filled('to') ? $request->to : null;
        //ORDERS
        $query = Order::query()
            ->where(
                'source',
                $sourceName
            )
            ->select([
                'id',
                'item_id',
                'option_id',
                'type',
                'item_name',
                'start_date',
                'qty_adult',
                'qty_child',
                'total_amount',
                'status',
                'currency',
                'date',
            ]);
        //FROM DATE
        if ($fromDate) {
            $query->whereDate(
                'date',
                '>=',
                $fromDate
            );
        }
        //TO DATE
        if ($toDate) {
            $query->whereDate(
                'date',
                '<=',
                $toDate
            );
        }
        //GET BOOKINGS
        $orders = $query->orderByDesc('id')->get();
        //PRICE CALCULATION
        $bookings = $orders->map(
            function ($order) {
                $price =
                    BookingPriceHelper::calculate(
                        $order
                    );
                return [
                    'order_id' => $order->id,
                    'type' => $order->type ?? 'N/A',
                    'name' => $order->item_name ?? 'N/A',
                    'travel_date' => $order->start_date,
                    'adult' => $price['adult'],
                    'child' => $price['child'],
                    'adult_net' => $price['adult_net'],
                    'child_net' => $price['child_net'],
                    'net_amount' => $price['net_amount'],
                    'sell_amount' => $price['sell_amount'],
                    'discount' => $price['discount'],
                    'margin' => $price['margin'],
                    'status' => $order->status ?? 'N/A',
                    'currency' => $price['currency'],
                ];
            }
        );
        //TOTALS
        $totalNet = $bookings->sum('net_amount');
        $totalSell = $bookings->sum('sell_amount');
        $totalAdult = $bookings->sum('adult');
        $totalChild = $bookings->sum('child');
        $totalProfit = $bookings->sum('margin');
        $totalPax = $totalAdult + $totalChild;
        //STATEMENT NUMBER
        $statementNo =
            'SRC-' .
            now()->format('Ymd') .
            '-' .
            strtoupper(
                substr(
                    md5($sourceName),
                    0,
                    5
                )
            );
        return view('admin.finances.soruces-summaries.source-booking-details', compact('sourceName','fromDate','toDate','bookings','totalNet','totalSell','totalAdult','totalChild','totalProfit','totalPax','statementNo'));
    }
}