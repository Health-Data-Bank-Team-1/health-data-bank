<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->withPersonalTeam()->create();

        $this->call(DiffUserWithRolesSeeder::class);
        $this->call(FormTemplateSeeder::class);
        $this->call(UserHealthEntriesSeeder::class);
        $this->call(ReportSeeder::class);
        $this->call(ProviderWithPatients::class);

    }
}
