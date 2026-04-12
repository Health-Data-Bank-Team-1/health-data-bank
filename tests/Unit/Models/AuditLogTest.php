<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\AuditLog;
use App\Models\Account;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function audit_log_can_be_created()
    {
        $account = Account::factory()->create();
        $log = AuditLog::factory()->create(['actor_id' => $account->id]);

        $this->assertNotNull($log->id);
        $this->assertEquals($account->id, $log->actor_id);
    }

    /** @test */
    public function audit_log_belongs_to_actor_account()
    {
        $account = Account::factory()->create();
        $log = AuditLog::factory()->create(['actor_id' => $account->id]);

        $this->assertTrue($log->actor()->exists());
        $this->assertEquals($account->id, $log->actor->id);
    }
}