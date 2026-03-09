<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Services\CohortFilterBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CohortFilterBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_all_accounts_when_no_filters_are_given(): void
    {
        Account::factory()->count(3)->create();

        $builder = app(CohortFilterBuilder::class);

        $results = $builder->build()->get();

        $this->assertCount(3, $results);
    }

    public function test_it_filters_by_account_type(): void
    {
        Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $builder = app(CohortFilterBuilder::class);

        $results = $builder->build([
            'account_type' => 'Researcher',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Researcher', $results->first()->account_type);
    }

    public function test_it_filters_by_account_status(): void
    {
        Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        Account::factory()->create([
            'account_type' => 'User',
            'status' => 'DEACTIVATED',
        ]);

        $builder = app(CohortFilterBuilder::class);

        $results = $builder->build([
            'account_status' => 'DEACTIVATED',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('DEACTIVATED', $results->first()->status);
    }

    public function test_it_filters_by_created_date_range(): void
    {
        Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
            'created_at' => '2026-02-01 10:00:00',
            'updated_at' => '2026-02-01 10:00:00',
        ]);

        Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
            'created_at' => '2026-03-01 10:00:00',
            'updated_at' => '2026-03-01 10:00:00',
        ]);

        $builder = app(CohortFilterBuilder::class);

        $results = $builder->build([
            'created_from' => '2026-02-15',
            'created_to' => '2026-03-15',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('2026-03-01', $results->first()->created_at->toDateString());
    }

    public function test_it_combines_multiple_filters(): void
    {
        Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
            'created_at' => '2026-02-01 10:00:00',
            'updated_at' => '2026-02-01 10:00:00',
        ]);

        Account::factory()->create([
            'account_type' => 'User',
            'status' => 'DEACTIVATED',
            'created_at' => '2026-02-20 10:00:00',
            'updated_at' => '2026-02-20 10:00:00',
        ]);

        Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
            'created_at' => '2026-02-20 10:00:00',
            'updated_at' => '2026-02-20 10:00:00',
        ]);

        $builder = app(CohortFilterBuilder::class);

        $results = $builder->build([
            'account_type' => 'User',
            'account_status' => 'DEACTIVATED',
            'created_from' => '2026-02-15',
            'created_to' => '2026-02-28',
        ])->get();

        $this->assertCount(1, $results);

        $account = $results->first();
        $this->assertEquals('User', $account->account_type);
        $this->assertEquals('DEACTIVATED', $account->status);
    }
}
