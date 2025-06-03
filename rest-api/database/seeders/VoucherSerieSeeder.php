<?php

namespace Database\Seeders;

use App\Models\VoucherSeries;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VoucherSerieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VoucherSeries::create([
            'voucher_type' => 1,
            'serie' => 'B001',
            'next_correlative' => 1,
            'is_active' => true,
        ]);
        VoucherSeries::create([
            'voucher_type' => 2,
            'serie' => 'F001',
            'next_correlative' => 1,
            'is_active' => true,
        ]);
    }
}
