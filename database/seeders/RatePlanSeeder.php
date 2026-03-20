<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class RatePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

public function run()
{
    DB::table('rate_plans')->insert([
        // Standard Room (id = 5)
        [
            'room_id' => 5,
            'name' => 'EP',
            'meal_type' => 'none',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'room_id' => 5,
            'name' => 'CP',
            'meal_type' => 'breakfast',
            'created_at' => now(),
            'updated_at' => now()
        ],

        // Deluxe Room (id = 6)
        [
            'room_id' => 6,
            'name' => 'CP',
            'meal_type' => 'breakfast',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'room_id' => 6,
            'name' => 'MAP',
            'meal_type' => 'all_meals',
            'created_at' => now(),
            'updated_at' => now()
        ],
    ]);
}
}
