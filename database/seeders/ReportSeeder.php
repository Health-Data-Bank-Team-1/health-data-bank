<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AggregatedData;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        AggregatedData::factory()->count(6)->create();
    }
}
