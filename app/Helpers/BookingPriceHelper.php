<?php

namespace App\Helpers;

use App\Models\AddDeal;
use App\Models\AddTourPrice;
use App\Models\AddTransfer;
use App\Models\FerriPrice;
use Carbon\Carbon;

class BookingPriceHelper
{
    //Function for net rate calculate
    public static function calculate($order): array {
        $type = strtolower(trim($order->type ?? ''));

        $adult = (int) ($order->qty_adult ?? 0);
        $child = (int) ($order->qty_child ?? 0);

        $adultNet = 0;
        $childNet = 0;

        //TOUR
        if (
            str_contains($type, 'tour') ||
            str_contains($type, 'activity')
        ) {
            $travelDate = !empty($order->start_date)
                ? Carbon::parse($order->start_date)->format('Y-m-d')
                : null;

            //Adult Price
            if ($order->item_id && $adult > 0 && $travelDate) {

                $adultPrice = AddTourPrice::query()
                    ->where('add_tour_id', $order->item_id)
                    ->where('type', 1)
                    ->whereDate('add_rate_from', '<=', $travelDate)
                    ->whereDate('add_rate_to', '>=', $travelDate)
                    ->where('min_qyt', '<=', $adult)
                    ->where('max_qyt', '>=', $adult)
                    ->when(
                        !empty($order->option_id),
                        function ($query) use ($order) {
                            $query->where(function ($q) use ($order) {
                                $q->where('option_id', $order->option_id)
                                    ->orWhereNull('option_id')
                                    ->orWhere('option_id', '');
                            });
                        }
                    )
                    ->orderByDesc('add_tour_price_id')
                    ->first();

                if ($adultPrice) {
                    $adultNet =
                        (float) $adultPrice->net_price * $adult;
                }
            }

            //Child Price
            if ($order->item_id && $child > 0 && $travelDate) {

                $childPrice = AddTourPrice::query()
                    ->where('add_tour_id', $order->item_id)
                    ->where('type', 0)
                    ->whereDate('add_rate_from', '<=', $travelDate)
                    ->whereDate('add_rate_to', '>=', $travelDate)
                    ->where('min_qyt', '<=', $child)
                    ->where('max_qyt', '>=', $child)
                    ->when(
                        !empty($order->option_id),
                        function ($query) use ($order) {
                            $query->where(function ($q) use ($order) {
                                $q->where('option_id', $order->option_id)
                                    ->orWhereNull('option_id')
                                    ->orWhere('option_id', '');
                            });
                        }
                    )
                    ->orderByDesc('add_tour_price_id')
                    ->first();

                if ($childPrice) {
                    $childNet =
                        (float) $childPrice->net_price * $child;
                }
            }
        }
        //FERRY
        elseif (str_contains($type, 'ferry')) {
            if (!empty($order->item_id)) {
                $ferryPrice = FerriPrice::query()
                    ->where('ferry_id', $order->item_id)
                    ->orderByDesc('price_id')
                    ->first();
                if ($ferryPrice) {
                    $unitPrice = (float) (
                        $ferryPrice->net_price ?? 0
                    );
                    $adultNet = $unitPrice * $adult;
                    $childNet = $unitPrice * $child;
                }
            }
        }
        //TRANSFER
        elseif (str_contains($type, 'transfer')) {
            if (!empty($order->item_id)) {
                $transfer = AddTransfer::query()
                    ->where(
                        'add_transfer_id',
                        $order->item_id
                    )
                    ->first();
                if ($transfer) {
                    $adultNet = (float) (
                        $transfer->price_for_car ?? 0
                    );
                    $childNet = 0;
                }
            }
        }
        //TOTAL NET
        $netAmount = $adultNet + $childNet;
        //ORIGINAL SELL AMOUNT
        $originalSellAmount = (float) ($order->total_amount ?? 0 );
        //TOUR DISCOUNT
        $discount = self::getTourDiscount($order);
        //FINAL SELL AMOUNT
        $sellAmount = $originalSellAmount + $discount;
        //MARGIN
        $margin = $sellAmount - $netAmount;
        return [
            'adult' => $adult,
            'child' => $child,
            'adult_net' => round($adultNet,2),
            'child_net' => round($childNet, 2),
            'net_amount' => round($netAmount, 2),
            'sell_amount' => round($sellAmount, 2),
            'discount' => round($discount, 2),
            'margin' => round($margin, 2),
            'currency' => $order->currency ?? 'THB',
        ];
    }

    //Function for get tour discount
    private static function getTourDiscount($order): float {
        $type = strtolower(trim($order->type ?? ''));
        //Discount tour
        if ($type !== 'tour' || empty($order->item_id)) {
            return 0;
        }
        $bookingDate = !empty($order->date)
            ? Carbon::parse($order->date)->format('Y-m-d')
            : now()->format('Y-m-d');
        return (float) (
            AddDeal::query()
                ->where('item_id', $order->item_id)
                ->whereRaw(
                    'LOWER(TRIM(item_type)) = ?',
                    ['tour']
                )
                ->where('status', 1)
                ->whereDate(
                    'validupto',
                    '>=',
                    $bookingDate
                )
                ->orderByDesc('add_deal_id')
                ->value('discount') ?? 0
        );
    }
}