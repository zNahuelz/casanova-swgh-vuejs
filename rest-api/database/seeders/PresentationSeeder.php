<?php

namespace Database\Seeders;

use App\Models\Presentation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PresentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Presentation::create([
            'name' => 'UNIDAD',
            'numeric_value' => 1,
            'aux' => '-----'
        ]);

        Presentation::create([
            'name' => 'CÁPSULAS',
            'numeric_value' => 10,
            'aux' => 'UNDS. - 250MG'
        ]);

        Presentation::create([
            'name' => 'TABLETAS',
            'numeric_value' => 20,
            'aux' => 'UNDS. - 500MG'
        ]);

        Presentation::create([
            'name' => 'CÁPSULA BLANDA',
            'numeric_value' => 20,
            'aux' => 'UNDS. - 500MG'
        ]);

        Presentation::create([
            'name' => 'POLVO - BOLSA',
            'numeric_value' => 500,
            'aux' => 'GR.'
        ]);

        Presentation::create([
            'name' => 'GRANOS - BOLSA',
            'numeric_value' => 500,
            'aux' => 'GR.'
        ]);

        Presentation::create([
            'name' => 'BOLSA',
            'numeric_value' => 0.5,
            'aux' => 'KG.'
        ]);

        Presentation::create([
            'name' => 'BOLSA',
            'numeric_value' => 1,
            'aux' => 'KG.'
        ]);

        Presentation::create([
            'name' => 'AMPOLLA',
            'numeric_value' => 5,
            'aux' => 'ML.'
        ]);
    }
}
