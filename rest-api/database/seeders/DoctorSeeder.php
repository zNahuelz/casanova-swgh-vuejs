<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Doctor::create([
            'name' => 'ABRAHAM',
            'paternal_surname' => 'VALVERDE',
            'maternal_surname' => 'SALAZAR',
            'dni' => '07866543',
            'email' => 'ABRAHAMVAL@ALTERNATIVAC.COM',
            'phone' => '+51999000111',
            'address' => 'Av. Globo Terraqueo 203',
            'user_id' => 2,
        ]);

        Doctor::create([
            'name' => 'JIMENA',
            'paternal_surname' => 'CHAVEZ',
            'maternal_surname' => 'VEGA',
            'dni' => '08812006',
            'email' => 'JIMENACHA@ALTERNATIVAC.COM',
            'phone' => '+51888000222',
            'address' => 'Jiron las Retamas 104',
            'user_id' => 3,
        ]);

        Doctor::create([
            'name' => 'JESUS',
            'paternal_surname' => 'SANCHEZ',
            'maternal_surname' => 'RAMIREZ',
            'dni' => '01288006',
            'email' => 'JESUSRAM@ALTERNATIVAC.COM',
            'phone' => '+51777000333',
            'address' => 'Calle Ancash 107',
            'user_id' => 4,
        ]);
    }
}
