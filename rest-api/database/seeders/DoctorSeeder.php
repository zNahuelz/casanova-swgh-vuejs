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
            'email' => 'DOCTOR@SITE.COM',
            'phone' => '+51999000111',
            'address' => 'Av. Globo Terraqueo 203',
            'user_id' => 2,
        ]);
    }
}
