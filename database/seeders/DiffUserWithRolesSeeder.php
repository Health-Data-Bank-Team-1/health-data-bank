<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DiffUserWithRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Team-friendly:
        // - Devs can set DEMO_SEED_PASSWORD in .env to whatever they want (e.g. Password4)
        // - Only falls back to a default in local environment
        $demoPassword = env('DEMO_SEED_PASSWORD');

        if (! $demoPassword && app()->environment('local')) {
            $demoPassword = 'Password4';
        }

        // If still not set (e.g., CI), we still set something deterministic
        // so seeded logins are usable if needed.
        if (! $demoPassword) {
            $demoPassword = 'password';
        }

        // ----
        // Helper: ensure role exists with UUID id
        // ----
        $ensureRole = function (string $name, string $guard = 'web'): Role {
            $role = Role::where('name', $name)->where('guard_name', $guard)->first();

            if (! $role) {
                $role = new Role();
                $role->id = (string) Str::uuid();
                $role->name = $name;
                $role->guard_name = $guard;
                $role->save();
            }

            return $role;
        };

        $ensureRole('user');
        $ensureRole('researcher');
        $ensureRole('admin');
        $ensureRole('provider');

        $upsertDemoUser = function (
            string $name,
            string $email,
            string $accountType,
            string $roleName
        ) use ($demoPassword): User {
            $account = Account::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email' => $email,
                    'account_type' => $accountType,
                    'status' => 'ACTIVE',
                ]
            );

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email' => $email,
                    'account_id' => $account->id,
                    'password' => Hash::make($demoPassword),
                ]
            );

            // Safer than syncRoles(): doesn’t remove other roles a teammate/seeder may add
            if (! $user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }

            return $user;
        };

        $upsertDemoUser('User Demo', 'user@demo.com', 'User', 'user');
        $upsertDemoUser('Researcher Demo', 'researcher@demo.com', 'Researcher', 'researcher');
        $upsertDemoUser('Admin Demo', 'admin@demo.com', 'Admin', 'admin');
        $upsertDemoUser('Provider Demo', 'provider@demo.com', 'HealthcareProvider', 'provider');

        // Optional: print the credentials during CLI seeding (helps teammates)
        if ($this->command) {
            $this->command->info('Demo accounts seeded/updated: user@demo.com, researcher@demo.com, admin@demo.com, provider@demo.com');
            $this->command->info('Demo password set from DEMO_SEED_PASSWORD (or local default).');
        }
    }
}