<?php

namespace App\Actions\Fortify;

use App\Models\Account;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

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
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'location' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:user,researcher,provider'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            $accountType = match ($input['role']) {
                'researcher' => 'Researcher',
                'provider' => 'HealthcareProvider',
                default => 'User',
            };

            $account = Account::create([
                'account_type' => $accountType,
                'name' => $input['name'],
                'email' => $input['email'],
                'date_of_birth' => $input['date_of_birth'],
                'gender' => $input['gender'],
                'status' => 'ACTIVE',
                'location' => $input['location'] ?? null,
            ]);

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'account_id' => $account->id,
            ]);

            $user->syncRoles([$input['role']]);

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
            'name' => explode(' ', $user->name, 2)[0] . "'s Team",
            'personal_team' => true,
        ]));
    }
}
