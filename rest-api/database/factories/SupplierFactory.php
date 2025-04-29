<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'ruc' => $this->generateUniqueRUC(),
            'address' => $this->faker->streetAddress(),
            'phone' => $this->faker->numerify('9########'),
            'email' => $this->faker->unique()->safeEmail(),
            'description' => $this->faker->text(100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    protected function generateUniqueRUC(): string 
    {
        $prefix = $this->faker->randomElement(['10','20']);
        $digits = $this->faker->numerify('#########');
        return $prefix . $digits;
    }
}
