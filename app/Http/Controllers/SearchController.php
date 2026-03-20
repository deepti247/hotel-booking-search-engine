<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        //  Validate request
        $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1|max:3'
        ]);

        $checkIn  = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $guests   = $request->guests;

        $nights = $checkIn->diffInDays($checkOut);

        $rooms = DB::table('rooms')->get();

        $results = [];

       $inventoryData = DB::table('room_inventory')
        ->whereBetween('date', [$checkIn->format('Y-m-d'),
        $checkOut->copy()->subDay()->format('Y-m-d')])
        ->get()
        ->groupBy('room_id');

        foreach ($rooms as $room) {

            // Get inventory for full date range
            /*$inventory = DB::table('room_inventory')
                ->where('room_id', $room->id)
                ->whereBetween('date', [
                    $checkIn->format('Y-m-d'),
                    $checkOut->copy()->subDay()->format('Y-m-d')
                ])
                ->get();*/
            $inventory = $inventoryData[$room->id] ?? collect();    

            // If data missing for any date → treat as unavailable
            if ($inventory->count() != $nights) {
                $isAvailable = false;
                $availableRooms = 0;
            } else {

                $isAvailable = true;
                $availableRooms = null;

                foreach ($inventory as $day) {

                    $available = $day->total_rooms - $day->booked_rooms;

                    if ($available <= 0) {
                        $isAvailable = false;
                    }

                    // get minimum availability across all days
                    if (is_null($availableRooms) || $available < $availableRooms) {
                        $availableRooms = $available;
                    }
                }
            }

            // Pricing only if available
            if ($isAvailable) {

                // Base price based on guests
                if ($guests == 1) {
                    $basePrice = $room->price_1_person;
                } elseif ($guests == 2) {
                    $basePrice = $room->price_2_person;
                } else {
                    $basePrice = $room->price_3_person;
                }

                // Room only & breakfast
                $roomOnlyPrice = $basePrice;
                $withBreakfastPrice = $basePrice + $room->breakfast_price;

                // Total price
                $roomOnlyTotal = $roomOnlyPrice * $nights;
                $withBreakfastTotal = $withBreakfastPrice * $nights;

                // Discounts
                $discountPercent = 0;

                $discountPercent = 0;

            // Get all discounts
            $discounts = DB::table('discounts')->get();

            $daysBefore = now()->diffInDays($checkIn, false);

           

            $discountPercent = 0;

            foreach ($discounts as $discount) {

                // Long Stay
                if ($discount->type == 'long_stay') {

                    if (
                        $nights >= $discount->min_nights &&
                        is_null($discount->max_days_before_checkin)
                    ) {
                        $discountPercent = max($discountPercent, $discount->discount_percent);
                    }
                }

                // Last Minute
                if ($discount->type == 'last_minute') {

                    if ($daysBefore <= $discount->max_days_before_checkin) {
                        $discountPercent = max($discountPercent, $discount->discount_percent);
                    }
                }
            }  

                // Final prices
                $roomOnlyFinal = $roomOnlyTotal - ($roomOnlyTotal * $discountPercent / 100);
                $withBreakfastFinal = $withBreakfastTotal - ($withBreakfastTotal * $discountPercent / 100);

            } else {
                //  Sold out case
                $roomOnlyPrice = null;
                $withBreakfastPrice = null;
                $roomOnlyTotal = null;
                $withBreakfastTotal = null;
                $roomOnlyFinal = null;
                $withBreakfastFinal = null;
                $discountPercent = 0;
            }

            //  Final response
            $results[] = [
                'room_type' => $room->room_type,
                'available_rooms' => $availableRooms ?? 0,
                'is_available' => $isAvailable,
                'status' => $isAvailable ? 'Available' : 'Sold Out',
                'nights' => $nights,
                'guests' => $guests,

                'options' => [
                    [
                        'type' => 'room_only',
                        'price_per_night' => $roomOnlyPrice,
                        'original_total' => $roomOnlyTotal,
                        'discount_percent' => $discountPercent,
                        'final_price' => $roomOnlyFinal ? round($roomOnlyFinal) : null
                    ],
                    [
                        'type' => 'with_breakfast',
                        'price_per_night' => $withBreakfastPrice,
                        'original_total' => $withBreakfastTotal,
                        'discount_percent' => $discountPercent,
                        'final_price' => $withBreakfastFinal ? round($withBreakfastFinal) : null
                    ]
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }
}