<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            [
                'name' => 'AÑO NUEVO',
                'date' => '2025-01-01',
                'is_recurring' => true,
            ],
            [
                'name' => 'NAVIDAD',
                'date' => '2025-12-25',
                'is_recurring' => true,
            ],
            [
                'name' => 'NAVIDAD II',
                'date' => '2025-12-26',
                'is_recurring' => true,
            ],
            [
                'name' => 'INMACULADA CONCEPCIÓN',
                'date' => '2025-12-08',
                'is_recurring' => true,
            ],
            [
                'name' => 'BATALLA DE AYACUCHO',
                'date' => '2025-12-09',
                'is_recurring' => true,
            ],
            [
                'name' => 'DÍA DE TODOS LOS SANTOS',
                'date' => '2025-11-01',
                'is_recurring' => true,
            ],
            [
                'name' => 'COMBATE DE ANGAMOS',
                'date' => '2025-10-08',
                'is_recurring' => true,
            ],
            [
                'name' => 'SANTA ROSA DE LIMA',
                'date' => '2025-08-30',
                'is_recurring' => true,
            ],
            [
                'name' => 'BATALLA DE JUNÍN',
                'date' => '2025-08-06',
                'is_recurring' => true,
            ],
            [
                'name' => 'FIESTAS PATRIAS I',
                'date' => '2025-07-28',
                'is_recurring' => true,
            ],
            [
                'name' => 'FIESTAS PATRIAS II',
                'date' => '2025-07-29',
                'is_recurring' => true
            ],
            [
                'name' => 'DÍA DE LA FUERZA AÉREA',
                'date' => '2025-07-23',
                'is_recurring' => true,
            ],
            [
                'name' => 'SAN PEDRO Y SAN PABLO',
                'date' => '2025-06-29',
                'is_recurring' => true,
            ],
            [
                'name' => 'DÍA DE LA BANDERA & BATALLA DE ARICA',
                'date' => '2025-06-07',
                'is_recurring' => true,
            ]
        ];

        foreach ($holidays as $h) {
            Holiday::create([
                'name' => $h['name'],
                'date' => $h['date'],
                'is_recurring' => $h['is_recurring'],
            ]);
        }
    }
}
