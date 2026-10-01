<?php

namespace Tests\Feature;

use App\Models\RentalSession;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveRentalPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_active_unit_is_shown_as_a_card(): void
    {
        $admin = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => Role::ADMIN])->id]);

        $playingUnit = Unit::create(['name' => 'PS Main', 'unit_code' => 'PS-MAIN', 'status' => Unit::STATUS_PLAYING, 'is_active' => true]);
        $idleUnit = Unit::create(['name' => 'PS Idle', 'unit_code' => 'PS-IDLE', 'status' => Unit::STATUS_AVAILABLE, 'is_active' => true]);
        Unit::create(['name' => 'PS Off', 'unit_code' => 'PS-OFF', 'status' => Unit::STATUS_AVAILABLE, 'is_active' => false]);

        RentalSession::create([
            'unit_id' => $playingUnit->id,
            'admin_id' => $admin->id,
            'customer_name' => 'Budi Rental',
            'rental_start_time' => now(),
            'rental_end_time' => now()->addHour(),
            'duration_minutes' => 60,
            'total_price' => 10000,
            'status' => RentalSession::STATUS_ACTIVE,
        ]);

        foreach ([route('admin.rentals.index'), route('admin.rentals.data')] as $url) {
            $this->actingAs($admin)->get($url)
                ->assertOk()
                ->assertSee('Budi Rental')
                ->assertSee(route('admin.billing.create', ['unit_id' => $idleUnit->id]), false)
                ->assertDontSee('PS Off');
        }
    }
}
