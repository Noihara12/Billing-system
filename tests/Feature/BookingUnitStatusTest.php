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
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingUnitStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Unit $unit;

    private PriceList $oneHour;

    private PriceList $threeHours;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-01 10:00'));

        $this->admin = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => Role::ADMIN])->id]);
        $category = Category::factory()->create();
        $this->unit = Unit::create(['category_id' => $category->id, 'name' => 'PS 01', 'unit_code' => 'PS-01', 'status' => Unit::STATUS_AVAILABLE, 'is_active' => true]);
        $this->oneHour = PriceList::create(['category_id' => $category->id, 'label' => '1 Jam', 'duration_minutes' => 60, 'price' => 10000, 'is_active' => true]);
        $this->threeHours = PriceList::create(['category_id' => $category->id, 'label' => '3 Jam', 'duration_minutes' => 180, 'price' => 30000, 'is_active' => true]);

        $this->booking = Booking::create([
            'booking_code' => 'BK-TEST',
            'user_id' => $this->admin->id,
            'unit_id' => $this->unit->id,
            'price_list_id' => $this->oneHour->id,
            'customer_name' => 'Pelanggan',
            'whatsapp_number' => '0811',
            'booking_date' => '2026-10-01',
            'start_time' => Carbon::parse('2026-10-01 12:00'),
            'end_time' => Carbon::parse('2026-10-01 13:00'),
            'duration_minutes' => 60,
            'total_price' => 10000,
            'status' => Booking::STATUS_PENDING,
        ]);
    }

    public function test_confirming_a_later_booking_keeps_unit_available_for_billing(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.bookings.confirm', $this->booking));

        $this->assertSame(Unit::STATUS_AVAILABLE, $this->unit->fresh()->status);
        $this->actingAs($this->admin)->get(route('admin.billing.create'))
            ->assertOk()
            ->assertSee('data-max-minutes="105"', false);
    }

    public function test_unit_becomes_booked_shortly_before_booking_and_is_released_after_cancel(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01 11:44'));
        $this->actingAs($this->admin)->get(route('admin.rentals.index'));
        $this->assertSame(Unit::STATUS_AVAILABLE, $this->unit->fresh()->status);

        $this->travelTo(Carbon::parse('2026-10-01 11:45'));
        $this->actingAs($this->admin)->get(route('admin.rentals.index'));
        $this->assertSame(Unit::STATUS_BOOKED, $this->unit->fresh()->status);

        $this->actingAs($this->admin)->patch(route('admin.bookings.cancel', $this->booking));
        $this->assertSame(Unit::STATUS_AVAILABLE, $this->unit->fresh()->status);
    }

    public function test_walk_in_that_would_overlap_next_booking_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.billing.start-walkin'), [
            'unit_id' => $this->unit->id,
            'price_list_id' => $this->threeHours->id,
            'customer_name' => 'Walk-in',
        ])->assertSessionHasErrors('price_list_id');

        $this->assertSame(0, RentalSession::count());
    }

    public function test_walk_in_whatsapp_number_is_optional_but_must_be_digits_when_filled(): void
    {
        $this->actingAs($this->admin)->post(route('admin.billing.start-walkin'), [
            'unit_id' => $this->unit->id,
            'price_list_id' => $this->oneHour->id,
            'customer_name' => 'Walk-in',
            'whatsapp_number' => 'nomor saya',
        ])->assertSessionHasErrors('whatsapp_number');

        $this->actingAs($this->admin)->post(route('admin.billing.start-walkin'), [
            'unit_id' => $this->unit->id,
            'price_list_id' => $this->oneHour->id,
            'customer_name' => 'Walk-in',
            'whatsapp_number' => '+62 812-3456-7890',
        ])->assertSessionHasNoErrors();

        $this->assertSame('081234567890', RentalSession::sole()->whatsapp_number);
    }

    public function test_walk_in_that_ends_before_next_booking_is_allowed(): void
    {
        $this->actingAs($this->admin)->post(route('admin.billing.start-walkin'), [
            'unit_id' => $this->unit->id,
            'price_list_id' => $this->oneHour->id,
            'customer_name' => 'Walk-in',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, RentalSession::count());
        $this->assertSame(Unit::STATUS_PLAYING, $this->unit->fresh()->status);
    }
}
