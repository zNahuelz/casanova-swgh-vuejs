<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentType::create([
            'name' => 'EFECTIVO'
        ]);
        PaymentType::create([
            'name' => 'TARJETA BANCARIA'
        ]);
        PaymentType::create([
            'name' => 'YAPE'
        ]);
        PaymentType::create([
            'name' => 'PLIN'
        ]);
    }
}
