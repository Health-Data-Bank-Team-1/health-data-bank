<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Str;
use App\Models\HealthEntry;
use Carbon\Carbon;

class ProviderWithPatients extends Seeder
{
    public function run(): void
    {
        $provider_account = Account::factory()->create([
            'name' => 'Test Patients',
            'email' => 'patients@example.com',
            'account_type' => 'HealthcareProvider',
            'status' => 'ACTIVE',
        ]);

        $provider = User::factory()->withPersonalTeam()->create([
            'name' => 'Test Patients',
            'email' => 'patients@example.com',
            'account_id' => $provider_account->id,
        ]);

        Role::firstOrCreate(
            ['name' => 'provider', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $provider->assignRole('provider');

        for ($i = 0; $i < 10; ++$i) {

            $account = Account::factory()->create([
                'account_type' => 'User',
                'status' => 'ACTIVE',
            ]);

            $user = User::factory()->create([
                'account_id' => $account->id,
            ]);

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

            $provider_account->patients()->attach($account->id);
        }
    }
}
