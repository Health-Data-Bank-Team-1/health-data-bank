<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\HealthEntry;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Role;

class UserHealthEntriesSeeder extends Seeder
{
    public function run(): void
    {


        $account = Account::factory()->create([
            'name' => 'Test Summary',
            'email' => 'summary@example.com',
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->withPersonalTeam()->create([
            'name' => 'Test Summary',
            'email' => 'summary@example.com',
            'account_id' => $account->id,
        ]);

        Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $user->assignRole('user');

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-01 10:00:00'),
            'encrypted_values' => ['weight' => 170, 'meals_per_day' => 2],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-02 10:00:00'),
            'encrypted_values' => ['weight' => 174, 'meals_per_day' => 3],
        ]);
    }
}
