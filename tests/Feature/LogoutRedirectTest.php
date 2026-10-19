<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LogoutRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_sends_user_to_the_home_page(): void
    {
        $customer = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => Role::CUSTOMER])->id]);

        $this->actingAs($customer)
            ->post(route('logout'))
            ->assertRedirect(route('dashboard'));

        $this->assertGuest();
        $this->assertFalse(Auth::check());
    }
}
