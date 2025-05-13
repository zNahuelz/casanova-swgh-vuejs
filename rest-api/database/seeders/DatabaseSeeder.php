<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            HolidaySeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            DoctorSeeder::class,
            WorkerSeeder::class,
            PatientSeeder::class,
            PresentationSeeder::class,
            TreatmentSeeder::class,
            VoucherTypeSeeder::class,
            PaymentTypeSeeder::class,
            SupplierSeeder::class,
            MedicineSeeder::class,
        ]);
    }
}
