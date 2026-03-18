<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\HealthGoal;
use App\Services\GoalProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_is_zero_when_no_entries_exist(): void
    {
        $account = Account::factory()->create();

        $goal = HealthGoal::create([
            'account_id' => $account->id,
            'metric_key' => 'exercise_minutes',
            'comparison_operator' => '>=',
            'target_value' => 30,
            'timeframe' => 'day',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'status' => 'ACTIVE',
        ]);

        $service = new GoalProgressService();

        $result = $service->calculate($goal);

        $this->assertEquals(0, $result['current_value']);
        $this->assertEquals(0, $result['progress_percent']);
        $this->assertFalse($result['is_met']);
    }

    public function test_progress_calculates_sum_of_entries(): void
    {
        $account = Account::factory()->create();

        $goal = HealthGoal::create([
            'account_id' => $account->id,
            'metric_key' => 'exercise_minutes',
            'comparison_operator' => '>=',
            'target_value' => 30,
            'timeframe' => 'day',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-03-10 10:00:00',
            'encrypted_values' => [
                'exercise_minutes' => 10,
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-03-11 10:00:00',
            'encrypted_values' => [
                'exercise_minutes' => 20,
            ],
        ]);

        $service = new GoalProgressService();

        $result = $service->calculate($goal);

        $this->assertEquals(30, $result['current_value']);
        $this->assertTrue($result['is_met']);
        $this->assertEquals(2, $result['entry_count']);
    }

    public function test_entries_outside_date_range_are_ignored(): void
    {
        $account = Account::factory()->create();

        $goal = HealthGoal::create([
            'account_id' => $account->id,
            'metric_key' => 'exercise_minutes',
            'comparison_operator' => '>=',
            'target_value' => 30,
            'timeframe' => 'day',
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-20',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-01 10:00:00',
            'encrypted_values' => [
                'exercise_minutes' => 100,
            ],
        ]);

        $service = new GoalProgressService();

        $result = $service->calculate($goal);

        $this->assertEquals(0, $result['current_value']);
        $this->assertEquals(0, $result['entry_count']);
    }
}
