<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'PS 4', 'description' => 'PlayStation 4', 'sort_order' => 1],
            ['name' => 'PS 5', 'description' => 'PlayStation 5', 'sort_order' => 2],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
