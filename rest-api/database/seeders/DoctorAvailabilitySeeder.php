<?php

namespace Database\Seeders;

use App\Models\DoctorAvailability;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctorsIds = [100, 110, 120];
        $weekdays = [1, 2, 3, 4, 5];
        foreach ($doctorsIds as $id)
        {
            foreach ($weekdays as $day) 
            {
                DoctorAvailability::create([
                    'doctor_id' => $id,
                    'weekday' => $day,
                    'start_time' => '09:00',
                    'end_time' => '18:00',
                    'break_start' => '13:00',
                    'break_end' => '13:40',
                    'is_active' => true,
                ]);
            }
        }
    }
}
