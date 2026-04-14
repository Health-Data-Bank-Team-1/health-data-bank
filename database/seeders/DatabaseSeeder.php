<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $this->call(FormTemplateSeeder::class);
        $this->call(UserHealthEntriesSeeder::class);
        $this->call(ReportSeeder::class);
        $this->call(ProviderWithPatients::class);
        $this->call(FlaggedSubmissionSeeder::class,);

        // Run demo users LAST so other seeders can’t overwrite their password/roles.
        $this->call(DiffUserWithRolesSeeder::class);
    }
}