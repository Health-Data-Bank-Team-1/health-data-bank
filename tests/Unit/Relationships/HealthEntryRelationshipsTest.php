<?php

namespace Tests\Unit\Relationships;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\HealthEntry;
use App\Models\Account;
use App\Models\FormSubmission;

class HealthEntryRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function health_entry_belongs_to_account()
    {
        $account = Account::factory()->create();
        $entry = HealthEntry::factory()->create(['account_id' => $account->id]);

        $this->assertTrue($entry->account()->exists());
        $this->assertEquals($account->id, $entry->account->id);
    }

    /** @test */
    public function health_entry_belongs_to_submission()
    {
        $submission = FormSubmission::factory()->create();
        $entry = HealthEntry::factory()->create(['submission_id' => $submission->id]);

        $this->assertTrue($entry->submission()->exists());
        $this->assertEquals($submission->id, $entry->submission->id);
    }
}