<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SearchController extends Controller
{
/**
 * ---------------------------------------------
 * Hotel Search API (RESTful Endpoint)
 * ---------------------------------------------
 *
 * Endpoint:
 * GET /api/search
 *
 * Description:
 * This API returns available hotel rooms based on
 * check-in date, check-out date, and number of guests.
 * It supports multiple room types, rate plans, pricing,
 * availability, and discounts.
 *
 * ---------------------------------------------
 * REQUEST PARAMETERS (Query Params)
 * ---------------------------------------------
 *
 * - check_in (required | date)
 *      Example: 2026-03-25
 *
 * - check_out (required | date | after:check_in)
 *      Example: 2026-03-28
 *
 * - guests (required | integer | min:1 | max:4)
 *      Number of guests for occupancy
 *
 * ---------------------------------------------
 * RESPONSE STRUCTURE (JSON)
 * ---------------------------------------------
 *
 * {
 *   "success": true,
 *   "data": [
 *     {
 *       "room_type": "deluxe",
 *       "available_rooms": 3,
 *       "is_available": true,
 *       "status": "Available",
 *       "nights": 2,
 *       "guests": 2,
 *       "rate_plans": [
 *         {
 *           "plan_name": "CP",
 *           "meal_type": "breakfast",
 *           "price_per_night": 3100,
 *           "original_total": 6200,
 *           "discount_percent": 10,
 *           "final_price": 5580
 *         }
 *       ]
 *     }
 *   ]
 * }
 *
 * ---------------------------------------------
 * BUSINESS LOGIC
 * ---------------------------------------------
 *
 * 1. Availability Check:
 *    - Uses room_inventory table
 *    - Ensures room is available for ALL selected dates
 *    - Calculates minimum available rooms across dates
 *
 * 2. Variable Occupancy:
 *    - Standard Room → max 3 guests
 *    - Deluxe Room → max 4 guests
 *    - Rooms exceeding capacity are excluded
 *
 * 3. Rate Plans:
 *    - Each room can have multiple rate plans (EP, CP, MAP)
 *    - Pricing is fetched from rate_plan_prices table
 *    - Based on occupancy (guests)
 *
 * 4. Pricing:
 *    - price_per_night × number_of_nights
 *
 * 5. Discounts:
 *    - Applied at rate-plan level
 *    - Example:
 *        EP  → 5% early bird
 *        CP  → 10%
 *        MAP → 10%
 *    - Uses best applicable discount (no stacking)
 *
 * ---------------------------------------------
 * REST API PRINCIPLES FOLLOWED
 * ---------------------------------------------
 *
 *  Stateless:
 *   Each request contains all required parameters
 *
 * Resource-Based:
 *   Endpoint represents a resource: /api/search
 *
 *  HTTP Method:
 *   GET is used for retrieving data (no modification)
 *
 *  JSON Response:
 *   Standard structured response for frontend consumption
 *
 *  Separation of Concerns:
 *   - Controller handles request/response
 *   - Database handles data logic
 *
 * ---------------------------------------------
 * NOTES
 * ---------------------------------------------
 *
 * - Designed for scalability (supports new rate plans & discounts)
 * - Optimized using grouped inventory queries
 * - Can be extended with filters, sorting, and pagination
 *
 */
    public function search(Request $request)
    {
        $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1|max:4'
        ]);

        $checkIn  = \Carbon\Carbon::parse($request->check_in);
        $checkOut = \Carbon\Carbon::parse($request->check_out);
        $guests   = $request->guests;

        $nights = $checkIn->diffInDays($checkOut);

        $rooms = DB::table('rooms')->get();

        // preload inventory
        $inventoryData = DB::table('room_inventory')
            ->whereBetween('date', [
                $checkIn->format('Y-m-d'),
                $checkOut->copy()->subDay()->format('Y-m-d')
            ])
            ->get()
            ->groupBy('room_id');

        $daysBefore = now()->diffInDays($checkIn, false);

        $results = [];

        foreach ($rooms as $room) {

            //  Skip if guests exceed capacity
            if ($guests > $room->max_person) {
                continue;
            }

            $inventory = $inventoryData[$room->id] ?? collect();

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

                    if (is_null($availableRooms) || $available < $availableRooms) {
                        $availableRooms = $available;
                    }
                }
            }

            $roomData = [
                'room_type' => $room->room_type,
                'available_rooms' => $availableRooms ?? 0,
                'is_available' => $isAvailable,
                'status' => $isAvailable ? 'Available' : 'Sold Out',
                'nights' => $nights,
                'guests' => $guests,
                'rate_plans' => []
            ];

            if ($isAvailable) {

                //  Get rate plans for this room
                $ratePlans = DB::table('rate_plans')
                    ->where('room_id', $room->id)
                    ->where('is_active', 1)
                    ->get();

                foreach ($ratePlans as $plan) {

                    // Get price for occupancy
                    $price = DB::table('rate_plan_prices')
                        ->where('rate_plan_id', $plan->id)
                        ->where('occupancy', $guests)
                        ->value('price');

                    if (!$price) {
                        continue; // skip if no price for this occupancy
                    }

                    $total = $price * $nights;

                    //  Get discounts for this plan
                    $discounts = DB::table('discounts')
                        ->where('status', 1)
                        ->where(function ($q) use ($plan) {
                            $q->where('rate_plan_id', $plan->id)
                                ->orWhereNull('rate_plan_id');
                        })
                        ->get();

                    $discountPercent = 0;

                    foreach ($discounts as $discount) {

                        // Early Bird
                        if ($discount->type == 'early_bird') {
                            if ($daysBefore >= $discount->max_days_before_checkin) {
                                $discountPercent = $discount->discount_percent;
                            }
                        }

                        // Long Stay
                        if ($discount->type == 'long_stay') {
                            if ($nights >= $discount->min_nights) {
                                $discountPercent = $discount->discount_percent;
                            }
                        }

                        // Last Minute
                        if ($discount->type == 'last_minute') {
                            if ($daysBefore <= $discount->max_days_before_checkin) {
                                $discountPercent = $discount->discount_percent;
                            }
                        }
                    }                                

                    $finalPrice = $total - ($total * $discountPercent / 100);

                    $roomData['rate_plans'][] = [
                        'plan_name' => $plan->name,
                        'meal_type' => $plan->meal_type,
                        'price_per_night' => $price,
                        'original_total' => $total,
                        'discount_percent' => $discountPercent,
                        'final_price' => round($finalPrice)
                    ];
                }
            }

            $results[] = $roomData;
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }
    
}
