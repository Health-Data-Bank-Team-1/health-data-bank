<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ First: create roles
        DB::table('roles')->insert([
            ['name' => 'User'],
            ['name' => 'Researcher'],
            ['name' => 'Healthcare Provider'],
            ['name' => 'Administrator'],
        ]);

        // 2️⃣ Then create a test user
        User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => 1, // Default = User
        ]);
    }
}