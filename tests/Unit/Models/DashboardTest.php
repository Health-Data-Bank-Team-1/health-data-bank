<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Dashboard;
use App\Models\Account;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dashboard_belongs_to_account()
    {
        $account = Account::factory()->create();
        $dashboard = Dashboard::factory()->create(['account_id' => $account->id]);

        $this->assertTrue($dashboard->account()->exists());
        $this->assertEquals($account->id, $dashboard->account->id);
    }

    /** @test */
    public function dashboard_uses_uuid_as_primary_key()
    {
        $dashboard = Dashboard::factory()->create();
        
        $this->assertTrue(\Illuminate\Support\Str::isUuid($dashboard->id));
    }
}