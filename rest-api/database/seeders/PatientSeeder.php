<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Patient::create([
            'name' => 'CLIENTE',
            'paternal_surname' => 'ORDINARIO',
            'maternal_surname' => '---',
            'dni' => '00000000',
            'address' => '---',
        ]);

        Patient::create([
            'name' => 'JAVIER',
            'paternal_surname' => 'JIMENEZ',
            'maternal_surname' => 'CUEVA',
            'dni' => '08544102',
            'address' => 'Av. El Trebol 102',
        ]);
    }
}
