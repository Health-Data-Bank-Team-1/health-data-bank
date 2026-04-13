<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Str;
use App\Models\HealthEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ProviderWithPatients extends Seeder
{
    public function run(): void
    {
        $providerEmail = 'patients@example.com';

        $provider_account = Account::updateOrCreate(
            ['email' => $providerEmail],
            [
                'name' => 'Test Patients',
                'email' => $providerEmail,
                'account_type' => 'HealthcareProvider',
                'status' => 'ACTIVE',
            ]
        );

        $provider = User::updateOrCreate(
            ['email' => $providerEmail],
            [
                'name' => 'Test Patients',
                'email' => $providerEmail,
                'account_id' => $provider_account->id,
                // optional, only if you need to log in as this provider:
                // 'password' => Hash::make(env('DEMO_SEED_PASSWORD', 'password')),
            ]
        );

        Role::firstOrCreate(
            ['name' => 'provider', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        if (! $provider->hasRole('provider')) {
            $provider->assignRole('provider');
        }

        for ($i = 0; $i < 10; ++$i) {
            $account = Account::factory()->create([
                'account_type' => 'User',
                'status' => 'ACTIVE',
            ]);

            User::factory()->create([
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

            // prevents duplicate pivot rows if this seeder re-runs
            $provider_account->patients()->syncWithoutDetaching([$account->id]);
        }
    }
}