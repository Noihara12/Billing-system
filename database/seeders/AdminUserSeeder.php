<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = Role::query()->where('name', Role::ADMIN)->value('id');

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'whatsapp_number' => '628000000000',
                'password' => 'Admin123!',
                'role_id' => $adminRoleId,
                'email_verified_at' => now(),
            ]
        );
    }
}
