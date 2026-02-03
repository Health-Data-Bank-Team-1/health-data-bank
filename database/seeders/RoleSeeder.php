<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['name' => 'User'],
            ['name' => 'Researcher'],
            ['name' => 'Healthcare Provider'],
            ['name' => 'Administrator'],
        ]);
    }
}