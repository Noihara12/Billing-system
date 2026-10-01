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

class BookingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Unit $unit;

    private PriceList $oneHour;

    private PriceList $twoHours;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-01 11:10'));

        $this->customer = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => Role::CUSTOMER])->id]);
        $category = Category::factory()->create();
        $this->unit = Unit::create([
            'category_id' => $category->id,
            'name' => 'PS 01',
            'unit_code' => 'PS-01',
            'status' => Unit::STATUS_PLAYING,
            'is_active' => true,
        ]);
        $this->oneHour = PriceList::create(['category_id' => $category->id, 'label' => '1 Jam', 'duration_minutes' => 60, 'price' => 10000, 'is_active' => true]);
        $this->twoHours = PriceList::create(['category_id' => $category->id, 'label' => '2 Jam', 'duration_minutes' => 120, 'price' => 20000, 'is_active' => true]);

        Booking::create([
            'booking_code' => 'BK-TEST',
            'user_id' => $this->customer->id,
            'unit_id' => $this->unit->id,
            'price_list_id' => $this->oneHour->id,
            'customer_name' => 'Pelanggan Lain',
            'whatsapp_number' => '0811',
            'booking_date' => '2026-10-01',
            'start_time' => Carbon::parse('2026-10-01 13:00'),
            'end_time' => Carbon::parse('2026-10-01 14:00'),
            'duration_minutes' => 60,
            'total_price' => 10000,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        RentalSession::create([
            'unit_id' => $this->unit->id,
            'admin_id' => $this->customer->id,
            'customer_name' => 'Walk-in',
            'rental_start_time' => Carbon::parse('2026-10-01 11:00'),
            'rental_end_time' => Carbon::parse('2026-10-01 12:00'),
            'duration_minutes' => 60,
            'total_price' => 10000,
            'status' => RentalSession::STATUS_ACTIVE,
        ]);
    }

    public function test_availability_marks_past_busy_and_too_short_slots(): void
    {
        $response = $this->actingAs($this->customer)->getJson(route('booking.availability', [
            'unit_id' => $this->unit->id,
            'date' => '2026-10-01',
            'price_list_id' => $this->twoHours->id,
        ]));

        $response->assertOk()
            ->assertJsonPath('open', '09:00')
            ->assertJsonPath('close', '24:00')
            ->assertJsonPath('busy', [
                ['start' => '11:00', 'end' => '12:00'],
                ['start' => '13:00', 'end' => '14:00'],
            ]);

        $slots = collect($response->json('slots'))->pluck('status', 'time');

        $this->assertCount(30, $slots);
        $this->assertSame('past', $slots['11:00']);
        $this->assertSame('booked', $slots['11:30']);
        $this->assertSame('insufficient', $slots['12:00']);
        $this->assertSame('booked', $slots['13:30']);
        $this->assertSame('available', $slots['14:00']);
        $this->assertSame('available', $slots['22:00']);
        $this->assertSame('insufficient', $slots['23:00']);
    }

    public function test_booking_is_rejected_when_slot_is_unavailable(): void
    {
        $cases = [
            ['2026-10-01', '11:30', 'active walk-in rental'],
            ['2026-10-01', '12:30', 'overlaps confirmed booking'],
            ['2026-10-01', '10:00', 'already passed'],
            ['2026-10-02', '08:00', 'before opening'],
            ['2026-10-02', '23:30', 'past closing'],
        ];

        foreach ($cases as [$date, $time, $reason]) {
            $this->actingAs($this->customer)
                ->post(route('booking.store'), $this->bookingPayload($date, $time))
                ->assertSessionHasErrors('start_time', "Expected rejection: {$reason}");
        }

        $this->assertSame(1, Booking::count());
    }

    public function test_booking_in_available_slot_is_created(): void
    {
        $this->actingAs($this->customer)
            ->post(route('booking.store'), $this->bookingPayload('2026-10-01', '14:00'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('bookings', [
            'unit_id' => $this->unit->id,
            'start_time' => '2026-10-01 14:00:00',
            'end_time' => '2026-10-01 15:00:00',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingPayload(string $date, string $time): array
    {
        return [
            'unit_id' => $this->unit->id,
            'price_list_id' => $this->oneHour->id,
            'booking_date' => $date,
            'start_time' => $time,
            'customer_name' => 'Customer',
            'whatsapp_number' => '081234567890',
        ];
    }
}
