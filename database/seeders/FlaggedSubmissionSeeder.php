<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\HealthEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FlaggedSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $userRole = Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $adminAccount = Account::firstOrCreate(
            ['email' => 'seed-admin@example.com'],
            [
                'id' => (string) Str::uuid(),
                'account_type' => 'Admin',
                'name' => 'Seed Admin',
                'status' => 'ACTIVE',
            ]
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'seed-admin@example.com'],
            [
                'id' => (string) Str::uuid(),
                'account_id' => $adminAccount->id,
                'name' => 'Seed Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        if (! $adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        $userAccount = Account::firstOrCreate(
            ['email' => 'flagged-user@example.com'],
            [
                'id' => (string) Str::uuid(),
                'account_type' => 'User',
                'name' => 'Flagged User',
                'status' => 'ACTIVE',
            ]
        );

        $template = FormTemplate::firstOrCreate(
            ['slug' => 'seeded-health-check'],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Seeded Health Check',
                'schema' => [],
                'description' => 'Seeded form for admin moderation testing.',
                'purpose' => 'Testing flagged submission rendering',
                'version' => 1,
                'approval_status' => 'approved',
                'approved_by' => $adminUser->id,
                'approved_at' => now(),
            ]
        );

        FormField::firstOrCreate(
            ['form_template_id' => $template->id, 'metric_key' => 'heart_rate'],
            [
                'id' => (string) Str::uuid(),
                'label' => 'Heart Rate',
                'field_type' => 'Number',
                'validation_rules' => [
                    'required' => true,
                    'numeric' => true,
                    'min' => 20,
                    'max' => 220,
                ],
                'goal_enabled' => false,
            ]
        );

        FormField::firstOrCreate(
            ['form_template_id' => $template->id, 'metric_key' => 'notes'],
            [
                'id' => (string) Str::uuid(),
                'label' => 'Notes',
                'field_type' => 'Text',
                'validation_rules' => ['nullable'],
                'goal_enabled' => false,
            ]
        );

        // Create multiple flagged submissions
        for ($i = 1; $i <= 5; $i++) {

            $submission = FormSubmission::create([
                'id' => (string) Str::uuid(),
                'account_id' => $userAccount->id,
                'form_template_id' => $template->id,
                'status' => 'FLAGGED',
                'submitted_at' => now()->subMinutes(60 - ($i * 5)),
                'flag_reason' => match ($i) {
                    1 => 'Abnormal heart rate detected.',
                    2 => 'Suspicious text input.',
                    3 => 'Empty or incomplete submission.',
                    4 => 'Unrealistic value detected.',
                    default => 'Possible accidental submission.',
                },
                'flagged_by' => $adminUser->id,
                'flagged_at' => now()->subMinutes(55 - ($i * 5)),
            ]);

            // Health Entry: Heart Rate
            HealthEntry::create([
                'id' => (string) Str::uuid(),
                'submission_id' => $submission->id,
                'account_id' => $userAccount->id,
                'timestamp' => now()->subMinutes(60 - ($i * 5)),
                'encrypted_values' => [
                    'field_id' => null,
                    'metric_key' => 'heart_rate',
                    'field_label' => 'Heart Rate',
                    'field_type' => 'Number',
                    'value' => match ($i) {
                        1 => 185,   // high
                        2 => 72,    // normal
                        3 => null,  // empty
                        4 => 999,   // unrealistic
                        default => 150,
                    },
                ],
            ]);

            // Health Entry: Notes
            HealthEntry::create([
                'id' => (string) Str::uuid(),
                'submission_id' => $submission->id,
                'account_id' => $userAccount->id,
                'timestamp' => now()->subMinutes(60 - ($i * 5)),
                'encrypted_values' => [
                    'field_id' => null,
                    'metric_key' => 'notes',
                    'field_label' => 'Notes',
                    'field_type' => 'Text',
                    'value' => match ($i) {
                        2 => 'test delete me please',
                        3 => '',
                        4 => 'Weight seems impossible',
                        default => 'Normal observation',
                    },
                ],
            ]);
        }
    }
}
