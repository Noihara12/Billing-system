<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PriceList;
use Illuminate\Database\Seeder;

class PriceListSeeder extends Seeder
{
    public function run(): void
    {
        $priceListsByCategory = [
            'PS 4' => [
                ['label' => '1 Jam', 'duration_minutes' => 60, 'price' => 10000, 'sort_order' => 1],
                ['label' => '2 Jam', 'duration_minutes' => 120, 'price' => 20000, 'sort_order' => 2],
                ['label' => '3 Jam', 'duration_minutes' => 180, 'price' => 30000, 'sort_order' => 3],
                ['label' => '5 Jam', 'duration_minutes' => 300, 'price' => 45000, 'sort_order' => 4],
            ],
            'PS 5' => [
                ['label' => '1 Jam', 'duration_minutes' => 60, 'price' => 15000, 'sort_order' => 1],
                ['label' => '2 Jam', 'duration_minutes' => 120, 'price' => 30000, 'sort_order' => 2],
                ['label' => '3 Jam', 'duration_minutes' => 180, 'price' => 45000, 'sort_order' => 3],
                ['label' => '5 Jam', 'duration_minutes' => 300, 'price' => 70000, 'sort_order' => 4],
            ],
        ];

        foreach ($priceListsByCategory as $categoryName => $priceLists) {
            $category = Category::query()->firstOrCreate(['name' => $categoryName]);

            foreach ($priceLists as $priceList) {
                PriceList::query()->updateOrCreate(
                    ['category_id' => $category->id, 'label' => $priceList['label']],
                    $priceList + ['is_active' => true]
                );
            }
        }
    }
}
