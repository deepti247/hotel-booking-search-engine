<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoomInventorySeeder extends Seeder
{
    public function run(): void
    {
        $rooms = DB::table('rooms')->get();

        $startDate = Carbon::create(2026, 3, 19);
        $endDate   = Carbon::create(2026, 4, 17);

        $data = [];

        foreach ($rooms as $room) {

            $date = $startDate->copy();

            while ($date <= $endDate) {

                $data[] = [
                    'room_id' => $room->id,
                    'date' => $date->format('Y-m-d'),

                    'total_rooms' => 5,
                    'booked_rooms' => 0,

                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $date->addDay();
            }
        }

        DB::table('room_inventory')->insert($data);
    }
}