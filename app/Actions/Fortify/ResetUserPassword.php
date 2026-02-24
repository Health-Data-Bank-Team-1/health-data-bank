<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use App\Services\AuditLogger;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();

        $actorId = null;

        if (!empty($user->email)) {
            $actorId = DB::table('accounts')
                ->where('email', $user->email)
                ->value('id');
        }

        AuditLogger::log(
            'password_reset_completed',
            'success',
            null,
            'account',
            $actorId,
            ['source' => 'fortify_reset'],
            $actorId
        );
    }
}
