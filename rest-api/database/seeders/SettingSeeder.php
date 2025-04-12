<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'key' => 'VALOR_IGV',
            'value' => '0.18',
        ]);
        Setting::create([
            'key' => 'VALOR_RUC',
            'value' => '20524871701',
        ]);
        Setting::create([
            'key' => 'VALOR_SEDE',
            'value' => 'Av. primavera 517, piso 1 oficina 103, San Borja - Lima , Lima, Peru',
        ]);
    }
}
