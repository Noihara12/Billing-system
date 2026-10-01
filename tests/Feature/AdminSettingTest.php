<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PriceList;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleName): User
    {
        return User::factory()->create(['role_id' => Role::firstOrCreate(['name' => $roleName])->id]);
    }

    public function test_customer_cannot_access_settings(): void
    {
        $this->actingAs($this->createUserWithRole(Role::CUSTOMER))
            ->get(route('admin.settings.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_view_current_operating_hours(): void
    {
        $this->actingAs($this->createUserWithRole(Role::ADMIN))
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('value="09:00"', false)
            ->assertSee('value="00:00"', false)
            ->assertSee('09:00 – 24:00');
    }

    public function test_admin_can_update_operating_hours(): void
    {
        $this->actingAs($this->createUserWithRole(Role::ADMIN))
            ->put(route('admin.settings.update'), ['booking_open_time' => '10:00', 'booking_close_time' => '22:00'])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasNoErrors();

        $this->assertSame('10:00', Setting::get('booking_open_time'));
        $this->assertSame('22:00', Setting::get('booking_close_time'));
    }

    public function test_invalid_time_is_rejected(): void
    {
        $this->actingAs($this->createUserWithRole(Role::ADMIN))
            ->put(route('admin.settings.update'), ['booking_open_time' => '25:00', 'booking_close_time' => ''])
            ->assertSessionHasErrors(['booking_open_time', 'booking_close_time']);

        $this->assertSame('09:00', Setting::get('booking_open_time'));
    }

    public function test_overnight_hours_allow_booking_after_midnight(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01 12:00'));
        Setting::set('booking_open_time', '10:00');
        Setting::set('booking_close_time', '02:00');

        $customer = $this->createUserWithRole(Role::CUSTOMER);
        $category = Category::factory()->create();
        $unit = Unit::create(['category_id' => $category->id, 'name' => 'PS 01', 'unit_code' => 'PS-01', 'status' => Unit::STATUS_AVAILABLE, 'is_active' => true]);
        $priceList = PriceList::create(['category_id' => $category->id, 'label' => '1 Jam', 'duration_minutes' => 60, 'price' => 10000, 'is_active' => true]);

        $slots = $this->actingAs($customer)
            ->getJson(route('booking.availability', ['unit_id' => $unit->id, 'date' => '2026-10-01', 'price_list_id' => $priceList->id]))
            ->assertJsonPath('open', '10:00')
            ->assertJsonPath('close', '02:00')
            ->json('slots');

        $statuses = collect($slots)->pluck('status', 'time');
        $this->assertSame('available', $statuses['00:30']);
        $this->assertSame('insufficient', $statuses['01:30']);

        $this->actingAs($customer)->post(route('booking.store'), [
            'unit_id' => $unit->id,
            'price_list_id' => $priceList->id,
            'booking_date' => '2026-10-01',
            'start_time' => '00:30',
            'customer_name' => 'Customer',
            'whatsapp_number' => '081234567890',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('bookings', [
            'unit_id' => $unit->id,
            'start_time' => '2026-10-02 00:30:00',
            'end_time' => '2026-10-02 01:30:00',
        ]);
    }
}
