<?php

namespace App\Actions\Fortify;

use App\Models\Account;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            // Create the domain Account record
            $account = Account::create([
                'account_type' => 'User',
                'name' => $input['name'],
                'email' => $input['email'],
                'status' => 'ACTIVE',
            ]);

            // Create the auth User linked to that account
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'account_id' => $account->id,
            ]);

            // Ensure role exists (roles.id has no default in this project; set it explicitly)
            $role = Role::where('name', 'user')->where('guard_name', 'web')->first();

            if (! $role) {
                $role = new Role();
                $role->id = (string) Str::uuid();
                $role->name = 'user';
                $role->guard_name = 'web';
                $role->save();
            }

            // Assign role
            $user->assignRole('user');

            // Jetstream personal team
            $this->createTeam($user);

            return $user;
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }
}