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
        // Use the same configurable demo password pattern as other demo seeders.
        $demoPassword = env('DEMO_SEED_PASSWORD');

        if (! $demoPassword && app()->environment('local')) {
            $demoPassword = 'Password4';
        }

        if (! $demoPassword) {
            $demoPassword = 'password';
        }

        $providerEmail = 'patients@example.com';

        // Provider demo account
        $provider_account = Account::updateOrCreate(
            ['email' => $providerEmail],
            [
                'name' => 'Test Patients',
                'email' => $providerEmail,
                'account_type' => 'HealthcareProvider',
                'status' => 'ACTIVE',
            ]
        );

        // Provider demo user (MUST set password)
        $provider = User::updateOrCreate(
            ['email' => $providerEmail],
            [
                'name' => 'Test Patients',
                'email' => $providerEmail,
                'account_id' => $provider_account->id,
                'password' => Hash::make($demoPassword),
            ]
        );

        // Ensure provider role exists (UUID id)
        Role::firstOrCreate(
            ['name' => 'provider', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        if (! $provider->hasRole('provider')) {
            $provider->assignRole('provider');
        }

        // Seed 10 patients with a couple health entries each
        for ($i = 0; $i < 10; ++$i) {
            $account = Account::factory()->create([
                'account_type' => 'User',
                'status' => 'ACTIVE',
            ]);

            // IMPORTANT: your DB requires users.password, so set it here too
            User::factory()->create([
                'account_id' => $account->id,
                'password' => Hash::make($demoPassword),
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

            // Avoid duplicate pivot rows if seeder is re-run
            $provider_account->patients()->syncWithoutDetaching([$account->id]);
        }
    }
}