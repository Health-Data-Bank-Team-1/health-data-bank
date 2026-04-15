<?php

namespace Database\Seeders;

use App\Models\AggregatedData;
use App\Models\TimeseriesData;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        AggregatedData::factory()->count(6)->create();

        $reports = \App\Models\Report::all();

        foreach ($reports as $report) {
            TimeseriesData::factory()->count(2)->create([
                'report_id' => $report->id,
            ]);
        }

        if ($reports->isEmpty()) {
            TimeseriesData::factory()->count(6)->create();
        }
    }
}
