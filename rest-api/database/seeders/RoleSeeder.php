<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'ADMINISTRADOR']);
        Role::create(['name' => 'DOCTOR']);
        Role::create(['name' => 'ENFERMERA']);
        Role::create(['name' => 'SECRETARIA']);
    }
}
