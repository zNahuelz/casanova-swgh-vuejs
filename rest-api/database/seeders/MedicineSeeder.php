<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicines = [
            [
                'name' => 'CITRATO DE MAGNESIO',
                'composition' => 'MAGNESIO',
                'description' => 'SUPLEMENTO DE MAGNECIO, CALCIO Y POTASIO',
                'barcode' => '0000000096754',
                'buy_price' => 10,
                'sell_price' => 20,
                'igv' => 3.6,
                'profit' => 6.4,
                'stock' => 100,
                'salable' => true,
                'presentation' => 5,
            ],
            [
                'name' => 'SAL ROSADA PREMIUM',
                'composition' => 'SAL ROSADA ANDINA',
                'description' => 'SAL ROSADA DE LOS ANDES....',
                'barcode' => '0000000013686',
                'buy_price' => 15,
                'sell_price' => 28,
                'igv' => 5.04,
                'profit' => 7.96,
                'stock' => 20,
                'salable' => true,
                'presentation' => 8,
            ],
            [
                'name' => 'ACEITE DE COPAIBA',
                'composition' => 'ACEITE DE COPAIBA 30ML',
                'description' => 'Ha sido utilizado por miles de años como regenerador celular, cicatrizante, antiséptico, antiinflamatorio y analgésico.',
                'barcode' => '0000000023996',
                'buy_price' => 26,
                'sell_price' => 35,
                'igv' => 6.3,
                'profit' => 2.7,
                'stock' => 50,
                'salable' => true,
                'presentation' => 1,
            ],
            [
                'name' => 'SUPER SILUETA, BATIDOS',
                'composition' => 'PENCA DE TUNA, HABAS, LINAZA, AVENA',
                'description' => 'Super Silueta es el batido que ayuda a acelerar tu metabolismo y a purificar el aparato digestivo.',
                'barcode' => '0000000012074',
                'buy_price' => 90,
                'sell_price' => 109,
                'igv' => 19.62,
                'profit' => 0.62,
                'stock' => 25,
                'salable' => true,
                'presentation' => 4,
            ],
            [
                'name' => 'VITAMINA B12 - MASON NATURAL',
                'composition' => 'VITAMINA B12',
                'description' => 'LIBRE DE GLUTEN SIN AZÚCAR SIN LACTEOS SIN PRESERVANTES',
                'barcode' => '0000000033108',
                'buy_price' => 81,
                'sell_price' => 94,
                'igv' => 16.92,
                'profit' => 3.92,
                'stock' => 12,
                'salable' => true,
                'presentation' => 2,
            ],
            [
                'name' => 'ZINC INYECTABLE',
                'composition' => 'ZINC ----',
                'description' => 'ZINC ---- USO INTERNO',
                'barcode' => '0000000011074',
                'buy_price' => 25,
                'sell_price' => 25,
                'igv' => 0,
                'profit' => 0,
                'stock' => 50,
                'salable' => false,
                'presentation' => 9,
            ],
            [
                'name' => 'VITAMINA D INYECTABLE',
                'composition' => 'VITAMINA D ----',
                'description' => 'VITAMINA D INYECTABLE ---- USO INTERNO',
                'barcode' => '0000000077545',
                'buy_price' => 20,
                'sell_price' => 20,
                'igv' => 0,
                'profit' => 0,
                'stock' => 50,
                'salable' => false,
                'presentation' => 9,
            ],
            [
                'name' => 'VITAMINA B12 INYECTABLE',
                'composition' => 'VITAMINA B12 ----',
                'description' => 'VITAMINA B12 INYECTABLE ---- USO INTERNO',
                'barcode' => '0000000061852',
                'buy_price' => 20,
                'sell_price' => 20,
                'igv' => 0,
                'profit' => 0,
                'stock' => 24,
                'salable' => false,
                'presentation' => 9,
            ],
            [
                'name' => 'VITAMINA B50',
                'composition' => 'VITAMINA B50 ----',
                'description' => 'VITAMINA B50 INYECTABLE ---- USO INTERNO',
                'barcode' => '0000000065109',
                'buy_price' => 27,
                'sell_price' => 27,
                'igv' => 0,
                'profit' => 0,
                'stock' => 12,
                'salable' => false,
                'presentation' => 9,
            ],
            [
                'name' => 'CITRATO DE CALCIO',
                'composition' => 'CITRATO DE CALCIO ----',
                'description' => 'CITRATO DE CALCIO INYECTABLE ---- USO INTERNO',
                'barcode' => '0000000032105',
                'buy_price' => 17,
                'sell_price' => 17,
                'igv' => 0,
                'profit' => 0,
                'stock' => 5,
                'salable' => false,
                'presentation' => 9,
            ],
            [
                'name' => 'CREATINA MONOHIDRATADA',
                'composition' => 'CREATINA MONOHIDRATADA ----',
                'description' => 'CREATINA MONOHIDRATADA ---- USO INTERNO',
                'barcode' => '0000000054120',
                'buy_price' => 50,
                'sell_price' => 50,
                'igv' => 0,
                'profit' => 0,
                'stock' => 20,
                'salable' => false,
                'presentation' => 7,
            ],
            [
                'name' => 'ALCOHOL ISOPROPILICO 96% - 1LT',
                'composition' => 'ALCOHOL 96% ----',
                'description' => 'ALCOHOL DE 96 ---- USO INTERNO',
                'barcode' => '0000000011667',
                'buy_price' => 9,
                'sell_price' => 9,
                'igv' => 0,
                'profit' => 0,
                'stock' => 12,
                'salable' => false,
                'presentation' => 1,
            ],
        ];

        foreach ($medicines as $m) {
            $medicine = Medicine::create([
                'name' => $m['name'],
                'composition' => $m['composition'],
                'description' => $m['description'],
                'barcode' => $m['barcode'],
                'buy_price' => $m['buy_price'],
                'sell_price' => $m['sell_price'],
                'igv' => $m['igv'],
                'profit' => $m['profit'],
                'stock' => $m['stock'],
                'salable' => $m['salable'],
                'presentation' => $m['presentation'],
            ]);
            $randomSupplier = rand(1, 40);
            $medicine->suppliers()->sync([$randomSupplier]);
        }
    }
}
