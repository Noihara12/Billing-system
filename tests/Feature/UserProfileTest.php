<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_profile_page(): void
    {
        $role = Role::firstOrCreate(['name' => Role::CUSTOMER]);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'name' => 'John Doe',
            'whatsapp_number' => '08123456789',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('08123456789');
    }

    public function test_user_can_update_profile_info(): void
    {
        $role = Role::firstOrCreate(['name' => Role::CUSTOMER]);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'whatsapp_number' => '0811111111',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'whatsapp_number' => '0822222222',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@example.com', $user->email);
        $this->assertEquals('0822222222', $user->whatsapp_number);
    }

    public function test_user_can_update_password(): void
    {
        $role = Role::firstOrCreate(['name' => Role::CUSTOMER]);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('password_status');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $role = Role::firstOrCreate(['name' => Role::CUSTOMER]);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }
}
