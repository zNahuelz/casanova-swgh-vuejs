<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'email' => 'ADMIN@SITE.COM',
            'role_id' => 1,
        ]);

        User::create([
            'username' => 'doctor',
            'password' => Hash::make('doctor'),
            'email' => 'DOCTOR@SITE.COM',
            'role_id' => 2,
        ]);

        User::create([
            'username' => 'enfermera',
            'password' => Hash::make('enfermera'),
            'email' => 'ENFERMERA@SITE.COM',
            'role_id' => 3
        ]);

        User::create([
            'username' => 'secretaria',
            'password' => Hash::make('secretaria'),
            'email' => 'SECRETARIA@SITE.COM',
            'role_id' => 4,
        ]);
    }
}
