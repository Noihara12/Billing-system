<?php

namespace Tests\Feature;

use App\Http\Requests\User\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Game;
use App\Models\PriceList;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GuestBookingTest extends TestCase
{
    use RefreshDatabase;

    private Unit $unit;

    private PriceList $priceList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-01 10:00'));

        $category = Category::factory()->create();
        $this->unit = Unit::create(['category_id' => $category->id, 'name' => 'PS 01', 'unit_code' => 'PS-01', 'status' => Unit::STATUS_AVAILABLE, 'is_active' => true]);
        $this->priceList = PriceList::create(['category_id' => $category->id, 'label' => '1 Jam', 'duration_minutes' => 60, 'price' => 10000, 'is_active' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $time = '14:00', string $whatsapp = '081234567890'): array
    {
        return [
            'unit_id' => $this->unit->id,
            'price_list_id' => $this->priceList->id,
            'booking_date' => '2026-10-01',
            'start_time' => $time,
            'customer_name' => 'Tamu',
            'whatsapp_number' => $whatsapp,
        ];
    }

    public function test_guest_can_open_booking_page_and_availability(): void
    {
        $this->get(route('booking.create'))->assertOk()->assertSee('Cek Booking');
        $this->getJson(route('booking.availability', ['unit_id' => $this->unit->id, 'date' => '2026-10-01']))->assertOk();
    }

    public function test_guest_sees_unit_status_and_games_on_home_page(): void
    {
        $game = Game::create(['name' => 'Tekken 8', 'is_active' => true]);
        $this->unit->games()->sync([$game->id]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('PS 01')
            ->assertSee('AVAILABLE')
            ->assertSee('Tekken 8')
            ->assertSee(route('booking.create', ['unit_id' => $this->unit->id]), false);

        $this->getJson(route('dashboard.data'))->assertOk()->assertJsonPath('units.0.games.0', 'Tekken 8');
    }

    public function test_busy_unit_still_offers_booking_but_broken_unit_does_not(): void
    {
        $this->unit->update(['status' => Unit::STATUS_PLAYING]);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Booking untuk Jam Lain')
            ->assertSee(route('booking.create', ['unit_id' => $this->unit->id]), false);

        $this->unit->update(['status' => Unit::STATUS_MAINTENANCE]);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Sedang Tidak Tersedia')
            ->assertDontSee(route('booking.create', ['unit_id' => $this->unit->id]), false);
    }

    public function test_guest_booking_is_created_without_account_and_detail_is_shown(): void
    {
        $response = $this->post(route('booking.store'), $this->payload());

        $booking = Booking::sole();
        $this->assertNull($booking->user_id);
        $response->assertRedirect(route('booking.lookup.show', $booking));

        $this->get(route('booking.lookup.show', $booking))
            ->assertOk()
            ->assertSee($booking->booking_code)
            ->assertSee('PS 01');
    }

    public function test_detail_requires_matching_code_and_whatsapp(): void
    {
        $this->post(route('booking.store'), $this->payload());
        $booking = Booking::sole();

        $this->flushSession();
        $this->get(route('booking.lookup.show', $booking))->assertRedirect(route('booking.lookup'));

        $this->post(route('booking.lookup.store'), ['booking_code' => $booking->booking_code, 'whatsapp_number' => '089999999999'])
            ->assertSessionHasErrors('booking_code');
        $this->get(route('booking.lookup.show', $booking))->assertRedirect(route('booking.lookup'));

        // Nomor dengan format berbeda (+62) tetap dianggap sama.
        $this->post(route('booking.lookup.store'), ['booking_code' => strtolower($booking->booking_code), 'whatsapp_number' => '+62 812-3456-7890'])
            ->assertRedirect(route('booking.lookup.show', $booking));
        $this->get(route('booking.lookup.show', $booking))->assertOk();
    }

    public function test_whatsapp_number_must_be_a_valid_indonesian_number(): void
    {
        foreach (['08123', 'nomor saya', '12345678901'] as $invalid) {
            $this->post(route('booking.store'), $this->payload('14:00', $invalid))
                ->assertSessionHasErrors('whatsapp_number');
        }

        $this->assertSame(0, Booking::count());
    }

    public function test_whatsapp_number_is_stored_in_one_format(): void
    {
        $this->post(route('booking.store'), $this->payload('14:00', '+62 812-3456-7890'))
            ->assertSessionHasNoErrors();

        $this->assertSame('081234567890', Booking::sole()->whatsapp_number);
    }

    public function test_guest_can_cancel_verified_booking(): void
    {
        $this->post(route('booking.store'), $this->payload());
        $booking = Booking::sole();

        $this->patch(route('booking.lookup.cancel', $booking))->assertRedirect(route('booking.lookup.show', $booking));
        $this->assertSame(Booking::STATUS_CANCELLED, $booking->fresh()->status);
    }

    public function test_unverified_guest_cannot_cancel_booking(): void
    {
        $this->post(route('booking.store'), $this->payload());
        $booking = Booking::sole();

        $this->flushSession();
        $this->patch(route('booking.lookup.cancel', $booking))->assertRedirect(route('booking.lookup'));
        $this->assertSame(Booking::STATUS_PENDING, $booking->fresh()->status);
    }

    public function test_guest_is_limited_to_two_pending_bookings_per_whatsapp_number(): void
    {
        $this->post(route('booking.store'), $this->payload('14:00'))->assertSessionHasNoErrors();
        $this->post(route('booking.store'), $this->payload('16:00'))->assertSessionHasNoErrors();

        $this->post(route('booking.store'), $this->payload('18:00', '+62 812-3456-7890'))
            ->assertSessionHasErrors('whatsapp_number');

        $this->assertSame(StoreBookingRequest::MAX_ACTIVE_GUEST_BOOKINGS, Booking::count());
    }
}
