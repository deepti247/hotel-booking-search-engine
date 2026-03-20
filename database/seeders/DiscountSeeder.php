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
    
    public function run(): void
        {
            DB::table('discounts')->insert([
                [
                    'type' => 'long_stay',
                    'min_nights' => 3,
                    'discount_percent' => 10
                ],
                [
                    'type' => 'long_stay',
                    'min_nights' => 6,
                    'discount_percent' => 20
                ],
                [
                    'type' => 'last_minute',
                    'max_days_before_checkin' => 3,
                    'discount_percent' => 5
                ]
            ]);
        }
}
