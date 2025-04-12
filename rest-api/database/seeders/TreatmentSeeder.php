<?php

namespace Database\Seeders;

use App\Models\Treatment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Treatment::create([
            'name' => 'VITAMINA C INTRAVENOSA',
            'description' => 'Aplicación de vitamina C directamente en el torrente sanguineo.',
            'procedure' => '---',
            'price' => 120,
        ]);

        Treatment::create([
            'name' => 'APLICACIÓN DE SUERO DE VIDA I',
            'description' => 'Aplicación de diversos suplementos directamente en el torrente sanguineo.',
            'procedure' => '---',
            'price' => 210,
        ]);
    }
}
