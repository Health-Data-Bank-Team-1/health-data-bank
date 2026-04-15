<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::where('account_type', 'User')->get();

        foreach ($accounts as $account) {
            Notification::create([
                'account_id' => $account->id,
                'type' => 'reminder',
                'message' => 'Don’t forget to submit your health data today.',
                'link' => '/user/forms',
                'status' => 'unread',
            ]);

            Notification::create([
                'account_id' => $account->id,
                'type' => 'provider_feedback',
                'message' => 'Your provider added new feedback to your profile.',
                'link' => '/user/dashboard',
                'status' => 'unread',
            ]);

            Notification::create([
                'account_id' => $account->id,
                'type' => 'system',
                'message' => 'Your weekly health summary is ready to view.',
                'link' => '/user/my-progress',
                'status' => 'read',
            ]);
        }
    }
}
