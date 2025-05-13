<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = [
            [
                'name' => 'CLIENTE',
                'paternal_surname' => 'ORDINARIO',
                'maternal_surname' => '-----',
                'birth_date' => '2000-01-01',
                'dni' => '00000000',
                'address' => '-----'
            ],
            [
                'name' => 'JAVIER',
                'paternal_surname' => 'JIMENEZ',
                'maternal_surname' => 'CUEVA',
                'birth_date' => '1997-06-10',
                'dni' => '08544102',
                'address' => 'Av. El Trebol 102',
            ],
            [
                'name' => 'FERNANDA',
                'paternal_surname' => 'TORRES',
                'maternal_surname' => 'RAMIREZ',
                'birth_date' => '2000-10-12',
                'dni' => '08535143',
                'address' => 'Calle Ricardo Palma 201',
            ],
            [
                'name' => 'MARIA',
                'paternal_surname' => 'CHAVEZ',
                'maternal_surname' => 'VEGA',
                'birth_date' => '2003-12-07',
                'dni' => '08712345',
                'address' => 'Calle Las Brisas 107',
            ],
            [
                'name' => 'JAVIER',
                'paternal_surname' => 'VARGAS',
                'maternal_surname' => 'LLOSA',
                'birth_date' => '1995-04-03',
                'dni' => '07866123',
                'address' => 'Av. Mariscal Castilla 304',
            ],
            [
                'name' => 'ALEJANDRO',
                'paternal_surname' => 'RAMIREZ',
                'maternal_surname' => 'CORDOBA',
                'birth_date' => '1993-02-01',
                'dni' => '01233765',
                'address' => 'Jiron Arica 404',
            ],
            [
                'name' => 'ALVARO',
                'paternal_surname' => 'CUEVAS',
                'maternal_surname' => 'PALOMAR',
                'birth_date' => '1995-04-03',
                'dni' => '06530125',
                'address' => 'Pasaje San Ramon 101',
            ],
            [
                'name' => 'ORIANA',
                'paternal_surname' => 'SALAZAR',
                'maternal_surname' => 'MESA',
                'birth_date' => '2004-07-13',
                'dni' => '91545773',
                'address' => 'Calle Berlin 506',
            ],
            [
                'name' => 'TOMAS',
                'paternal_surname' => 'MASSA',
                'maternal_surname' => 'MILEI',
                'birth_date' => '1999-02-10',
                'dni' => '73422108',
                'address' => 'Calle Enrique Palacios 102',
            ],
            [
                'name' => 'JESUS',
                'paternal_surname' => 'SALGADO',
                'maternal_surname' => 'VELASQUEZ',
                'birth_date' => '2003-01-07',
                'dni' => '95677094',
                'address' => 'Calle Los Nogales 271',
            ],
            [
                'name' => 'FRANCISCO',
                'paternal_surname' => 'HERNANDEZ',
                'maternal_surname' => 'TALAVERA',
                'birth_date' => '2000-01-29',
                'dni' => '76799056',
                'address' => 'Av. Antonio Jose de Sucre 108',
            ],
            [
                'name' => 'JOSE',
                'paternal_surname' => 'PINOCHET',
                'maternal_surname' => 'DEL VALLE',
                'birth_date' => '1995-04-03',
                'dni' => '01233297',
                'address' => 'Av. Los Pinos 103',
            ],
            [
                'name' => 'DYLAN',
                'paternal_surname' => 'CHOTA',
                'maternal_surname' => 'SMITH',
                'birth_date' => '1990-07-14',
                'dni' => '07611063',
                'address' => 'ASENTAMIENTO HUMANO SAN JUDAS TADEO',
            ]
        ];

        foreach($patients as $p)
        {
            Patient::create([
                'name' => $p['name'],
                'paternal_surname' => $p['paternal_surname'],
                'maternal_surname' => $p['maternal_surname'],
                'birth_date' => $p['birth_date'],
                'dni' => $p['dni'],
                'address' => $p['address'],
            ]);
        }

    }
}
