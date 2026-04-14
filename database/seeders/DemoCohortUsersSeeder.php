<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoCohortUsersSeeder extends Seeder
{
    public function run(): void
    {
        $demoPassword = 'Password4';

        // Ensure 'user' role exists (UUID id)
        $role = Role::where('name', 'user')->where('guard_name', 'web')->first();
        if (! $role) {
            $role = new Role();
            $role->id = (string) Str::uuid();
            $role->name = 'user';
            $role->guard_name = 'web';
            $role->save();
        }

        // Create 12 ACTIVE "User" accounts + linked users
        for ($i = 1; $i <= 12; $i++) {
            $email = sprintf('cohort%02d@demo.com', $i);
            $name  = sprintf('Cohort User %02d', $i);

            $account = Account::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email' => $email,
                    'account_type' => 'User',
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

            $user->syncRoles(['user']);
        }
    }
}