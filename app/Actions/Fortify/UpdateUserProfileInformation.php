<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use App\Services\AuditLogger;


class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $oldName  = $user->name;
            $oldEmail = $user->email;
            $actorId = null;
            if (!empty($user->email)) {
                $actorId = DB::table('accounts')->where('email', $user->email)->value('id');
            }

            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();


            if ($actorId) {
                DB::table('accounts')
                    ->where('id', $actorId)
                    ->update([
                        'name' => $input['name'],
                        'email' => $input['email'],
                        'updated_at' => now(),
                    ]);
            }

            $fieldsUpdated = [];
            if (($input['name'] ?? null) !== $oldName)  $fieldsUpdated[] = 'name';
            if (($input['email'] ?? null) !== $oldEmail) $fieldsUpdated[] = 'email';

            AuditLogger::log(
                'profile_updated',
                'success',
                null,
                'account',
                $actorId,
                [
                    'fields_updated' => $fieldsUpdated,
                    'email_changed' => in_array('email', $fieldsUpdated, true),
                    'photo_updated' => isset($input['photo']),
                ],

                $actorId
            );
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $oldEmail = $user->email;
        $actorId = null;
        if (!empty($oldEmail)) {
            $actorId = DB::table('accounts')->where('email', $oldEmail)->value('id');
        }
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        if ($actorId) {
            DB::table('accounts')
                ->where('id', $actorId)
                ->update([
                    'name' => $input['name'],
                    'email' => $input['email'],
                    'updated_at' => now(),
                ]);
        }
        $user->sendEmailVerificationNotification();

        AuditLogger::log(
            'profile_updated',
            'success',
            null,
            'account',
            $actorId,
            [
                'fields_updated' => ['name', 'email'],
                'email_verification_reset' => true,
            ],
            $actorId
        );

    }
}
