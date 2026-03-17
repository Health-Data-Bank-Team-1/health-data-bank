<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

class DiffUserWithRolesSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $user->assignRole('user');

        $res = User::factory()->withPersonalTeam()->create([
            'name' => 'Test Res',
            'email' => 'res@example.com',
        ]);

        Role::firstOrCreate(
            ['name' => 'researcher', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $res->assignRole('researcher');

        $res = User::factory()->withPersonalTeam()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
        ]);

        Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $res->assignRole('admin');
    }
}
