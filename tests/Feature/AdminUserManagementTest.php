<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => Role::ADMIN]);

        return User::factory()->create([
            'role_id' => $role->id,
            'email' => 'admin_'.uniqid().'@example.com',
        ]);
    }

    private function createCustomer(): User
    {
        $role = Role::firstOrCreate(['name' => Role::CUSTOMER]);

        return User::factory()->create([
            'role_id' => $role->id,
            'email' => 'cust_'.uniqid().'@example.com',
        ]);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_users_list(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee($customer->name);
    }

    public function test_user_whatsapp_number_must_be_digits(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Nomor Salah',
            'email' => 'salah_'.uniqid().'@example.com',
            'whatsapp_number' => 'tidak ada nomor',
            'role_id' => Role::firstOrCreate(['name' => Role::CUSTOMER])->id,
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ])->assertSessionHasErrors('whatsapp_number');
    }

    public function test_admin_can_create_new_user(): void
    {
        $admin = $this->createAdmin();
        $customerRole = Role::firstOrCreate(['name' => Role::CUSTOMER]);

        $email = 'newuser_'.uniqid().'@example.com';
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New User Test',
            'email' => $email,
            'whatsapp_number' => '081987654321',
            'role_id' => $customerRole->id,
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => 'New User Test',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $customer), [
            'name' => 'Updated Customer Name',
            'email' => $customer->email,
            'whatsapp_number' => '0855555555',
            'role_id' => $customer->role_id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $customer->refresh();
        $this->assertEquals('Updated Customer Name', $customer->name);
        $this->assertEquals('0855555555', $customer->whatsapp_number);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));
        $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $customer));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    }
}
