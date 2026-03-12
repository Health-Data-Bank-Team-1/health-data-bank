<?php

namespace Tests\Unit\Relationships;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Account;
use App\Models\Dashboard;
use App\Models\HealthEntry;
use App\Models\FormSubmission;

class AccountRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function account_has_one_dashboard()
    {
        $account = Account::factory()->create();
        $dashboard = Dashboard::factory()->create(['account_id' => $account->id]);

        $this->assertTrue($account->dashboard()->exists());
        $this->assertEquals($dashboard->id, $account->dashboard->id);
    }

    /** @test */
    public function account_has_many_health_entries()
    {
        $account = Account::factory()->create();
        
        HealthEntry::factory()->count(3)->create(['account_id' => $account->id]);

        $this->assertCount(3, $account->healthEntries);
    }

    /** @test */
    public function account_has_many_form_submissions()
    {
        $account = Account::factory()->create();
        
        FormSubmission::factory()->count(5)->create(['account_id' => $account->id]);

        $this->assertCount(5, $account->submissions);
    }

    /** @test */
    public function dashboard_cascades_delete_when_account_deleted()
    {
        $account = Account::factory()->create();
        $dashboard = Dashboard::factory()->create(['account_id' => $account->id]);
        
        $account->delete();

        $this->assertFalse(Dashboard::where('id', $dashboard->id)->exists());
    }
}