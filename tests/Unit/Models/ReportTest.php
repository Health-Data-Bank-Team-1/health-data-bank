<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Report;
use App\Models\Account;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function report_can_be_created_with_valid_data()
    {
        $researcher = Account::factory()->create(['account_type' => 'Researcher']);
        $report = Report::factory()->create(['researcher_id' => $researcher->id]);

        $this->assertNotNull($report->id);
        $this->assertEquals($researcher->id, $report->researcher_id);
    }

    /** @test */
    public function report_can_have_different_types()
    {
        $report1 = Report::factory()->create(['report_type' => 'Aggregated']);
        $report2 = Report::factory()->create(['report_type' => 'Comparative']);

        $this->assertEquals('Aggregated', $report1->report_type);
        $this->assertEquals('Comparative', $report2->report_type);
    }

    /** @test */
    public function report_belongs_to_researcher()
    {
        $researcher = Account::factory()->create(['account_type' => 'Researcher']);
        $report = Report::factory()->create(['researcher_id' => $researcher->id]);

        $this->assertTrue($report->researcher()->exists());
        $this->assertEquals($researcher->id, $report->researcher->id);
    }
}