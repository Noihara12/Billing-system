<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Awalan "Kategori" agar tidak pernah bentrok dengan PS 4 / PS 5 bawaan migration.
            'name' => 'Kategori '.fake()->unique()->numberBetween(1, 9999),
            'description' => null,
            'sort_order' => 0,
        ];
    }
}
