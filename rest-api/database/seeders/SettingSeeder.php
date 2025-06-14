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
            'description' => 'Controla el valor del IGV en el sistema.'
        ]);
        Setting::create([
            'key' => 'VALOR_RUC',
            'value' => '20524871701',
            'description' => 'Controla el valor del RUC empresarial en el sistema, puede visualizarse en los comprobantes de pago.'
        ]);
        Setting::create([
            'key' => 'VALOR_SEDE',
            'value' => 'Av. primavera 517, piso 1 oficina 103, San Borja - Lima , Lima, Peru',
            'description' => 'Controla el valor de la dirección empresarial en el sistema, puede visualizarse en los comprobantes de pago.'
        ]);
        Setting::create([
            'key' => 'ESTADO_TRABAJO_FINDES',
            'value' => 'false',
            'description' => 'Permite habilitar o deshabilitar la posibilidad de asignar horario laboral los fines de semana. (Debe configurar el horario de cada doctor luego)'
        ]);
        Setting::create([
            'key' => 'COSTO_CITA_REGULAR',
            'value' => '120',
            'description' => 'Controla el costo de una consulta médica en el sistema.'
        ]);
    }
}
