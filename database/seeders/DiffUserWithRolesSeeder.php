<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Str;

class DiffUserWithRolesSeeder extends Seeder
{
    public function run(): void
    {
        $user_account = Account::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'account_id' => $user_account->id,
        ]);

        Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $user->assignRole('user');

        $res_account = Account::factory()->create([
            'name' => 'Test Res',
            'email' => 'res@example.com',
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $res = User::factory()->withPersonalTeam()->create([
            'name' => 'Test Res',
            'email' => 'res@example.com',
            'account_id' => $res_account->id,
        ]);

        Role::firstOrCreate(
            ['name' => 'researcher', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $res->assignRole('researcher');

        $admin_account = Account::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $admin = User::factory()->withPersonalTeam()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'account_id' => $admin_account->id,
        ]);

        Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $admin->assignRole('admin');

        $provider_account = Account::factory()->create([
            'name' => 'Test Provider',
            'email' => 'provider@example.com',
            'account_type' => 'HealthcareProvider',
            'status' => 'ACTIVE',
        ]);

        $provider = User::factory()->withPersonalTeam()->create([
            'name' => 'Test Provider',
            'email' => 'provider@example.com',
            'account_id' => $provider_account->id,
        ]);

        Role::firstOrCreate(
            ['name' => 'provider', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $provider->assignRole('provider');
    }
}
