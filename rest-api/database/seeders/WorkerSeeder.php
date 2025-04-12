<?php

namespace Database\Seeders;

use App\Enums\WorkerType;
use App\Models\Worker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Worker::create([
            'name' => 'ALEJANDRA',
            'paternal_surname' => 'SUAREZ',
            'maternal_surname' => 'MEDINA',
            'dni' => '07767554',
            'email' => 'ALEJANDRA.MED@GMAIL.COM',
            'phone' => '996413664',
            'address' => 'Av. El Trebol 201',
            'hiring_date' => '2020-04-10',
            'user_id' => 3,
            'position' => 'ENFERMERA',
        ]);

        Worker::create([
            'name' => 'MARIA',
            'paternal_surname' => 'CHAVEZ',
            'maternal_surname' => 'VARGAS',
            'dni' => '01655429',
            'email' => 'MARMOURNIER@OUTLOOK.COM',
            'phone' => '996125883',
            'address' => 'Pasaje Javier Heraud 104',
            'hiring_date' => '2018-12-04',
            'user_id' => 4,
            'position' => 'SECRETARIA',
        ]);
    }
}
