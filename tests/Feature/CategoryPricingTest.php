<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\PriceList;
use App\Models\RentalSession;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPricingTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function createUnit(Category $category): Unit
    {
        return Unit::create([
            'category_id' => $category->id,
            'name' => 'PS '.$category->id,
            'unit_code' => 'PS-'.$category->id,
            'status' => Unit::STATUS_AVAILABLE,
            'is_active' => true,
        ]);
    }

    private function createPriceList(Category $category, int $price): PriceList
    {
        return PriceList::create([
            'category_id' => $category->id,
            'label' => '1 Jam',
            'duration_minutes' => 60,
            'price' => $price,
            'is_active' => true,
        ]);
    }

    public function test_walk_in_rental_uses_price_from_unit_category(): void
    {
        $admin = $this->createUserWithRole(Role::ADMIN);
        $ps5 = Category::factory()->create();
        $unit = $this->createUnit($ps5);
        $priceList = $this->createPriceList($ps5, 15000);

        $response = $this->actingAs($admin)->post(route('admin.billing.start-walkin'), [
            'unit_id' => $unit->id,
            'price_list_id' => $priceList->id,
            'customer_name' => 'Walk-in',
        ]);

        $response->assertRedirect(route('admin.rentals.index'));
        $this->assertDatabaseHas('rental_sessions', [
            'unit_id' => $unit->id,
            'price_list_id' => $priceList->id,
            'total_price' => 15000,
        ]);
    }

    public function test_walk_in_rental_rejects_price_list_from_other_category(): void
    {
        $admin = $this->createUserWithRole(Role::ADMIN);
        $unit = $this->createUnit(Category::factory()->create());
        $otherCategoryPrice = $this->createPriceList(Category::factory()->create(), 10000);

        $response = $this->actingAs($admin)->post(route('admin.billing.start-walkin'), [
            'unit_id' => $unit->id,
            'price_list_id' => $otherCategoryPrice->id,
            'customer_name' => 'Walk-in',
        ]);

        $response->assertSessionHasErrors('price_list_id');
        $this->assertSame(0, RentalSession::count());
    }

    public function test_booking_rejects_price_list_from_other_category(): void
    {
        $customer = $this->createUserWithRole(Role::CUSTOMER);
        $unit = $this->createUnit(Category::factory()->create());
        $otherCategoryPrice = $this->createPriceList(Category::factory()->create(), 10000);

        $response = $this->actingAs($customer)->post(route('booking.store'), [
            'unit_id' => $unit->id,
            'price_list_id' => $otherCategoryPrice->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'customer_name' => 'Customer',
            'whatsapp_number' => '081234567890',
        ]);

        $response->assertSessionHasErrors('price_list_id');
        $this->assertSame(0, Booking::count());
    }

    public function test_unit_and_price_list_require_category(): void
    {
        $admin = $this->createUserWithRole(Role::ADMIN);

        $this->actingAs($admin)->post(route('admin.units.store'), [
            'name' => 'PS 09',
            'unit_code' => 'PS-09',
            'status' => Unit::STATUS_AVAILABLE,
        ])->assertSessionHasErrors('category_id');

        $this->actingAs($admin)->post(route('admin.price-lists.store'), [
            'label' => '1 Jam',
            'duration_minutes' => 60,
            'price' => 10000,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_category_in_use_cannot_be_deleted(): void
    {
        $admin = $this->createUserWithRole(Role::ADMIN);
        $category = Category::factory()->create();
        $this->createUnit($category);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_pages_with_category_fields_render(): void
    {
        $admin = $this->createUserWithRole(Role::ADMIN);
        $category = Category::factory()->create(['name' => 'PS 5 Pro']);
        $unit = $this->createUnit($category);
        $priceList = $this->createPriceList($category, 15000);

        foreach ([
            route('admin.categories.index'),
            route('admin.categories.edit', $category),
            route('admin.units.index'),
            route('admin.units.edit', $unit),
            route('admin.price-lists.index'),
            route('admin.price-lists.edit', $priceList),
            route('admin.billing.create'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk()->assertSee('PS 5 Pro');
        }

        $this->actingAs($this->createUserWithRole(Role::CUSTOMER))
            ->get(route('booking.create'))
            ->assertOk()
            ->assertSee('data-category="'.$category->id.'"', false);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $admin = $this->createUserWithRole(Role::ADMIN);
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('status');

        $this->assertModelMissing($category);
    }
}
