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

        Treatment::create([
            'name' => 'OZONOTERAPIA',
            'description' => 'Aplicación de ozono directamente en el torrente sanguineo.',
            'procedure' => '---',
            'price' => 150,
        ]);

        Treatment::create([
            'name' => 'VITAMINA B INTRAVENOSA',
            'description' => 'Aplicación de vitamina B directamente en el torrente sanguineo.',
            'procedure' => '---',
            'price' => 150,
        ]);

        Treatment::create([
            'name' => 'CÁMARA DE SAL - 1HR.',
            'description' => 'Espacio dónde el paciente respira aire cargado de sal durante 1 hora.',
            'procedure' => '---',
            'price' => 100,
        ]);

        Treatment::create([
            'name' => 'CÁMARA DE SAL - 30MIN.',
            'description' => 'Espacio dónde el paciente respira aire cargado de sal durante media hora.',
            'procedure' => '---',
            'price' => 85,
        ]);
    }
}
