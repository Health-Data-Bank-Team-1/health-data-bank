<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\HealthEntry;
use App\Models\Account;
use App\Models\FormSubmission;

class HealthEntryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function health_entry_can_store_encrypted_values_as_array()
    {
        $account = Account::factory()->create();
        $submission = FormSubmission::factory()->create(['account_id' => $account->id]);

        $entry = HealthEntry::create([
            'submission_id' => $submission->id,
            'account_id' => $account->id,
            'timestamp' => now(),
            'encrypted_values' => [
                'heart_rate' => 'encrypted_data',
                'blood_pressure' => 'encrypted_data',
            ],
        ]);

        $this->assertIsArray($entry->encrypted_values);
        $this->assertArrayHasKey('heart_rate', $entry->encrypted_values);
    }

    /** @test */
    public function health_entry_belongs_to_account()
    {
        $account = Account::factory()->create();
        $entry = HealthEntry::factory()->create(['account_id' => $account->id]);

        $this->assertTrue($entry->account()->exists());
        $this->assertEquals($account->id, $entry->account->id);
    }
}