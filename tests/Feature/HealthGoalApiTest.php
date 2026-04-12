<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\HealthEntry;
use App\Models\HealthGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthGoalApiTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithAccount(): array
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
            'email' => $account->email ?? fake()->safeEmail(),
        ]);

        if (!$account->email) {
            $account->email = $user->email;
            $account->save();
        }

        return [$user, $account];
    }

    private function createGoalEnabledMetric(string $metricKey = 'alcohol_consumption'): void
    {
        $template = FormTemplate::factory()->create([
            'title' => 'Test Goal Metric Template',
            'approval_status' => 'approved',
            'schema' => [],
        ]);

        FormField::create([
            'form_template_id' => $template->id,
            'label' => 'Alcohol Consumption',
            'metric_key' => $metricKey,
            'field_type' => 'number',
            'goal_enabled' => true,
            'validation_rules' => ['min' => 0],
        ]);
    }

    public function test_authenticated_user_can_list_their_goals(): void
    {
        [$user, $account] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        HealthGoal::create([
            'account_id' => $account->id,
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/goals')
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'goal' => [
                        'id',
                        'account_id',
                        'metric_key',
                        'comparison_operator',
                        'target_value',
                        'timeframe',
                        'start_date',
                        'end_date',
                        'status',
                    ],
                    'progress' => [
                        'metric_key',
                        'timeframe',
                        'comparison_operator',
                        'target_value',
                        'current_value',
                        'progress_percent',
                        'is_met',
                        'entry_count',
                        'evaluated_from',
                        'evaluated_to',
                    ],
                ],
            ]);
    }

    public function test_authenticated_user_can_create_goal(): void
    {
        [$user, $account] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        $payload = [
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'status' => 'ACTIVE',
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/goals', $payload)
            ->assertCreated()
            ->assertJsonPath('goal.metric_key', 'alcohol_consumption')
            ->assertJsonPath('goal.target_value', 2);

        $this->assertDatabaseHas('health_goals', [
            'account_id' => $account->id,
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
        ]);
    }

    public function test_guest_cannot_create_goal(): void
    {
        $this->postJson('/api/goals', [])
            ->assertUnauthorized();
    }

    public function test_invalid_goal_payload_is_rejected(): void
    {
        [$user] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        $payload = [
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '!=',
            'target_value' => -1,
            'timeframe' => 'year',
            'start_date' => '2026-03-31',
            'end_date' => '2026-03-01',
            'status' => 'BAD',
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/goals', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'comparison_operator',
                'target_value',
                'timeframe',
                'end_date',
                'status',
            ]);
    }

    public function test_owner_can_view_single_goal(): void
    {
        [$user, $account] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        $goal = HealthGoal::create([
            'account_id' => $account->id,
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => null,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/goals/{$goal->id}")
            ->assertOk()
            ->assertJsonPath('goal.id', $goal->id);
    }

    public function test_user_cannot_view_another_users_goal(): void
    {
        [$userA] = $this->createUserWithAccount();
        [, $accountB] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        $goal = HealthGoal::create([
            'account_id' => $accountB->id,
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => null,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($userA, 'sanctum')
            ->getJson("/api/goals/{$goal->id}")
            ->assertNotFound();
    }

    public function test_owner_can_update_goal(): void
    {
        [$user, $account] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        $goal = HealthGoal::create([
            'account_id' => $account->id,
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => null,
            'status' => 'ACTIVE',
        ]);

        $payload = [
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 1,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => null,
            'status' => 'ACTIVE',
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/goals/{$goal->id}", $payload)
            ->assertOk()
            ->assertJsonPath('goal.target_value', 1);

        $this->assertDatabaseHas('health_goals', [
            'id' => $goal->id,
            'target_value' => 1,
        ]);
    }

    public function test_user_cannot_update_another_users_goal(): void
    {
        [$userA] = $this->createUserWithAccount();
        [, $accountB] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        $goal = HealthGoal::create([
            'account_id' => $accountB->id,
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => null,
            'status' => 'ACTIVE',
        ]);

        $payload = [
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 1,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => null,
            'status' => 'ACTIVE',
        ];

        $this->actingAs($userA, 'sanctum')
            ->putJson("/api/goals/{$goal->id}", $payload)
            ->assertNotFound();
    }

    public function test_goal_response_includes_progress_from_health_entries(): void
    {
        [$user, $account] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        $goal = HealthGoal::create([
            'account_id' => $account->id,
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 5,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-03-10 10:00:00',
            'encrypted_values' => [
                'alcohol_consumption' => 2,
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-03-12 10:00:00',
            'encrypted_values' => [
                'alcohol_consumption' => 1,
            ],
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/goals/{$goal->id}")
            ->assertOk()
            ->assertJsonPath('progress.current_value', 3)
            ->assertJsonPath('progress.entry_count', 2);
    }

    public function test_listing_goals_writes_audit_row(): void
    {
        [$user, $account] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        HealthGoal::create([
            'account_id' => $account->id,
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/goals')
            ->assertOk();

        $this->assertDatabaseHas('audits', [
            'event' => 'health_goal_index_viewed',
        ]);
    }

    public function test_creating_goal_writes_audit_row(): void
    {
        [$user, $account] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        $payload = [
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'status' => 'ACTIVE',
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/goals', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('audits', [
            'event' => 'health_goal_created',
        ]);
    }

    public function test_viewing_goal_writes_audit_row(): void
    {
        [$user, $account] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        $goal = HealthGoal::create([
            'account_id' => $account->id,
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => null,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/goals/{$goal->id}")
            ->assertOk();

        $this->assertDatabaseHas('audits', [
            'event' => 'health_goal_viewed',
        ]);
    }

    public function test_updating_goal_writes_audit_row(): void
    {
        [$user, $account] = $this->createUserWithAccount();
        $this->createGoalEnabledMetric();

        $goal = HealthGoal::create([
            'account_id' => $account->id,
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 2,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => null,
            'status' => 'ACTIVE',
        ]);

        $payload = [
            'metric_key' => 'alcohol_consumption',
            'comparison_operator' => '<=',
            'target_value' => 1,
            'timeframe' => 'week',
            'start_date' => '2026-03-01',
            'end_date' => null,
            'status' => 'ACTIVE',
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/goals/{$goal->id}", $payload)
            ->assertOk();

        $this->assertDatabaseHas('audits', [
            'event' => 'health_goal_updated',
        ]);
    }
}
