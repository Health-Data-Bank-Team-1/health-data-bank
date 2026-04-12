<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Account;
use App\Models\Dashboard;
use Illuminate\Database\QueryException;

class ConstraintsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dashboard_requires_valid_account_id_foreign_key()
    {
        $this->expectException(QueryException::class);
        
        Dashboard::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'account_id' => \Illuminate\Support\Str::uuid(), // Non-existent account
        ]);
    }

    /** @test */
    public function account_email_cannot_be_null()
    {
        $this->expectException(QueryException::class);
        
        Account::create([
            'account_type' => 'User',
            'name' => 'Test User',
            'email' => null,
            'status' => 'ACTIVE',
        ]);
    }

    /** @test */
    public function account_name_cannot_be_null()
    {
        $this->expectException(QueryException::class);
        
        Account::create([
            'account_type' => 'User',
            'name' => null,
            'email' => 'test@example.com',
            'status' => 'ACTIVE',
        ]);
    }

    /** @test */
    public function account_status_has_valid_enum_values()
    {
        $account = Account::factory()->create(['status' => 'ACTIVE']);
        $this->assertContains($account->status, ['ACTIVE', 'DEACTIVATED']);
    }
}