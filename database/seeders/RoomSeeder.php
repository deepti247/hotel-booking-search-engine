<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rooms')->insert([
            // Standard Room
            [
                'hotel_id' => 1,
                'room_type' => 'standard',
                'max_person' => 3,

                'price_1_person' => 2000,
                'price_2_person' => 2500,
                'price_3_person' => 3000,

                'breakfast_price' => 400,
                'total_rooms' => 5,

                'created_at' => now(),
                'updated_at' => now()
            ],

            // Deluxe Room
            [
                'hotel_id' => 1,
                'room_type' => 'deluxe',
                'max_person' => 3,

                'price_1_person' => 2000,
                'price_2_person' => 2500,
                'price_3_person' => 3000,

                'breakfast_price' => 400,
                'total_rooms' => 5,

                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}