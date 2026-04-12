<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Account;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function account_can_be_created_with_fillable_attributes()
    {
        $account = Account::create([
            'account_type' => 'User',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'ACTIVE',
        ]);

        $this->assertNotNull($account->id);
        $this->assertEquals('User', $account->account_type);
        $this->assertEquals('john@example.com', $account->email);
        $this->assertEquals('ACTIVE', $account->status);
    }

    /** @test */
    public function account_uses_uuid_as_primary_key()
    {
        $account = Account::factory()->create();
        
        $this->assertTrue(\Illuminate\Support\Str::isUuid($account->id));
    }

    /** @test */
    public function account_status_can_be_deactivated()
    {
        $account = Account::factory()->deactivated()->create();
        
        $this->assertEquals('DEACTIVATED', $account->status);
    }

    /** @test */
    public function account_email_is_unique()
    {
        Account::factory()->create(['email' => 'test@example.com']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        Account::factory()->create(['email' => 'test@example.com']);
    }

    /** @test */
    public function account_type_enum_accepts_valid_values()
    {
        $validTypes = ['User', 'Researcher', 'HealthcareProvider', 'Admin'];
        
        foreach ($validTypes as $type) {
            $account = Account::factory()->create(['account_type' => $type]);
            $this->assertEquals($type, $account->account_type);
        }
    }
}