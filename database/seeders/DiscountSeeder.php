<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    $plans = DB::table('rate_plans')->get();

    foreach ($plans as $plan) {

        // EP → 5% early bird
        if ($plan->name == 'EP') {
            DB::table('discounts')->insert([
                'rate_plan_id' => $plan->id,
                'type' => 'early_bird',
                'max_days_before_checkin' => 7,
                'discount_percent' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // CP & MAP → 10%
        if (in_array($plan->name, ['CP', 'MAP'])) {
            DB::table('discounts')->insert([
                'rate_plan_id' => $plan->id,
                'type' => 'early_bird',
                'max_days_before_checkin' => 7,
                'discount_percent' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
}
