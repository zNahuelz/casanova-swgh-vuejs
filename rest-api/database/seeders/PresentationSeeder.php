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
            'name' => 'CAPSULAS BLANDAS',
            'numeric_value' => 60,
            'aux' => 'UNDS.'
        ]);

        Presentation::create([
            'name' => 'FRASCO',
            'numeric_value' => 50,
            'aux' => 'ML.'
        ]);

        Presentation::create([
            'name' => 'POTE',
            'numeric_value' => 50,
            'aux' => 'GR.'
        ]);

        Presentation::create([
            'name' => 'CAJA',
            'numeric_value' => 14,
            'aux' => 'UNDS.'
        ]);
    }
}
