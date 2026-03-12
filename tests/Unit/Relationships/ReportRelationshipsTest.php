<?php

namespace Tests\Unit\Relationships;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Report;
use App\Models\Account;

class ReportRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function report_belongs_to_researcher_account()
    {
        $researcher = Account::factory()->create(['account_type' => 'Researcher']);
        $report = Report::factory()->create(['researcher_id' => $researcher->id]);

        $this->assertTrue($report->researcher()->exists());
        $this->assertEquals($researcher->id, $report->researcher->id);
    }

    /** @test */
    public function report_researcher_can_be_null()
    {
        $report = Report::factory()->create(['researcher_id' => null]);

        $this->assertNull($report->researcher_id);
    }
}