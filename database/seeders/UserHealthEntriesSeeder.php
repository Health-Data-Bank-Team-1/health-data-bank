<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\HealthEntry;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\FormTemplate;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\Notification;

class UserHealthEntriesSeeder extends Seeder
{
    public function run(): void
    {

        $account = Account::factory()->create([
            'name' => 'Test Summary',
            'email' => 'summary@example.com',
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->withPersonalTeam()->create([
            'name' => 'Test Summary',
            'email' => 'summary@example.com',
            'account_id' => $account->id,
        ]);

        Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $user->assignRole('user');

        Notification::create([
            'account_id' => $account->id,
            'type' => 'reminder',
            'message' => 'reminder test',
            'status' => 'unread'
        ]);

        Notification::create([
            'account_id' => $account->id,
            'type' => 'reminder',
            'message' => 'reminder test 2',
            'status' => 'unread'
        ]);

        Notification::create([
            'account_id' => $account->id,
            'type' => 'alert',
            'message' => 'alert',
            'status' => 'unread'
        ]);

        Notification::create([
            'account_id' => $account->id,
            'type' => 'alert',
            'message' => 'alert 2',
            'status' => 'unread'
        ]);

        Notification::create([
            'account_id' => $account->id,
            'type' => 'alert',
            'message' => 'alert 3',
            'status' => 'unread'
        ]);

        $template = FormTemplate::create([
            'title' => 'Test Template',
            'version' => 1,
            'approval_status' => 'approved',
            'description' => 'for testing',
        ]);

        FormField::create([
            'form_template_id' => $template->id,
            'label' => 'Weight',
            'field_type' => 'Number',
            'validation_rules' => ['required', 'integer', 'min:0'],
        ]);

        FormField::create([
            'form_template_id' => $template->id,
            'label' => 'Meals Per Day',
            'field_type' => 'Number',
            'validation_rules' => ['required', 'integer', 'min:0'],
        ]);

        FormField::create([
            'form_template_id' => $template->id,
            'label' => 'Heart Rate',
            'field_type' => 'Number',
            'validation_rules' => ['required', 'integer', 'min:0'],
        ]);

        $submission = FormSubmission::create([
            'account_id' => $account->id,
            'form_template_id' => $template->id,
            'status' => 'SUBMITTED',
            'submitted_at' => Carbon::parse('2026-04-01 10:00:00')
        ]);

        HealthEntry::factory()->create([
            'submission_id' => $submission->id,
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => ['weight' => 170, 'meals_per_day' => 2, 'hr' => 80],
        ]);

        $submission = FormSubmission::create([
            'account_id' => $account->id,
            'form_template_id' => $template->id,
            'status' => 'SUBMITTED',
            'submitted_at' => Carbon::parse('2026-04-02 10:00:00')
        ]);

        HealthEntry::factory()->create([
            'submission_id' => $submission->id,
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-02 10:00:00'),
            'encrypted_values' => ['weight' => 174, 'meals_per_day' => 3, 'hr' => 87],
        ]);

        $submission = FormSubmission::create([
            'account_id' => $account->id,
            'form_template_id' => $template->id,
            'status' => 'SUBMITTED',
            'submitted_at' => Carbon::parse('2026-04-03 10:00:00')
        ]);

        HealthEntry::factory()->create([
            'submission_id' => $submission->id,
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-03 10:00:00'),
            'encrypted_values' => ['weight' => 167, 'meals_per_day' => 2, 'hr' => 82],
        ]);

        $submission = FormSubmission::create([
            'account_id' => $account->id,
            'form_template_id' => $template->id,
            'status' => 'SUBMITTED',
            'submitted_at' => Carbon::parse('2026-04-04 10:00:00')
        ]);

        HealthEntry::factory()->create([
            'submission_id' => $submission->id,
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-04 10:00:00'),
            'encrypted_values' => ['weight' => 178, 'meals_per_day' => 4, 'hr' => 67],
        ]);

        $submission = FormSubmission::create([
            'account_id' => $account->id,
            'form_template_id' => $template->id,
            'status' => 'SUBMITTED',
            'submitted_at' => Carbon::parse('2026-04-05 10:00:00')
        ]);

        HealthEntry::factory()->create([
            'submission_id' => $submission->id,
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-05 10:00:00'),
            'encrypted_values' => ['weight' => 182, 'meals_per_day' => 1, 'hr' => 89],
        ]);

        $submission = FormSubmission::create([
            'account_id' => $account->id,
            'form_template_id' => $template->id,
            'status' => 'SUBMITTED',
            'submitted_at' => Carbon::parse('2026-04-06 10:00:00')
        ]);

        HealthEntry::factory()->create([
            'submission_id' => $submission->id,
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-06 10:00:00'),
            'encrypted_values' => ['weight' => 178, 'meals_per_day' => 2, 'hr' => 99],
        ]);

        $submission = FormSubmission::create([
            'account_id' => $account->id,
            'form_template_id' => $template->id,
            'status' => 'SUBMITTED',
            'submitted_at' => Carbon::parse('2026-04-07 10:00:00')
        ]);

        HealthEntry::factory()->create([
            'submission_id' => $submission->id,
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-07 10:00:00'),
            'encrypted_values' => ['weight' => 176, 'meals_per_day' => 2, 'hr' => 92],
        ]);
    }
}
