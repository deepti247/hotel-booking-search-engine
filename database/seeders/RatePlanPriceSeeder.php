<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatePlanPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    // Get plans
    $plans = DB::table('rate_plans')->get();

    foreach ($plans as $plan) {

        // STANDARD ROOM (room_id = 5)
        if ($plan->room_id == 5 && $plan->name == 'EP') {
            $prices = [
                [1, 2000], [2, 2500], [3, 3000]
            ];
        }

        if ($plan->room_id == 5 && $plan->name == 'CP') {
            $prices = [
                [1, 2400], [2, 2900], [3, 3400]
            ];
        }

        // DELUXE ROOM (room_id = 6)
        if ($plan->room_id == 6 && $plan->name == 'CP') {
            $prices = [
                [1, 2600], [2, 3100], [3, 3600], [4, 4000]
            ];
        }

        if ($plan->room_id == 6 && $plan->name == 'MAP') {
            $prices = [
                [1, 3000], [2, 3500], [3, 4000], [4, 4500]
            ];
        }

        foreach ($prices as $p) {
            DB::table('rate_plan_prices')->insert([
                'rate_plan_id' => $plan->id,
                'occupancy' => $p[0],
                'price' => $p[1],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
}
