<?php

namespace Tests\Feature\Suggestions;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Services\SuggestionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuggestionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_no_data_suggestion_when_no_entries_exist(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $svc = app(SuggestionService::class);

        $out = $svc->generateForAccount(
            $account->id,
            Carbon::parse('2026-04-01 00:00:00'),
            Carbon::parse('2026-04-07 00:00:00')
        );

        $this->assertCount(1, $out['suggestions']);
        $this->assertEquals('no_data', $out['suggestions'][0]['type']);
        $this->assertNull($out['suggestions'][0]['metric']);
    }

    public function test_it_returns_insufficient_data_suggestion_for_low_sample_count(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => ['hr' => 72],
        ]);

        $svc = app(SuggestionService::class);

        $out = $svc->generateForAccount(
            $account->id,
            Carbon::parse('2026-04-01 00:00:00'),
            Carbon::parse('2026-04-07 00:00:00')
        );

        $types = array_column($out['suggestions'], 'type');

        $this->assertContains('insufficient_data', $types);
    }

    public function test_it_returns_high_value_suggestion_when_average_exceeds_threshold(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => ['hr' => 90],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-02 10:00:00'),
            'encrypted_values' => ['hr' => 92],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-03 10:00:00'),
            'encrypted_values' => ['hr' => 94],
        ]);

        $svc = app(SuggestionService::class);

        $out = $svc->generateForAccount(
            $account->id,
            Carbon::parse('2026-04-01 00:00:00'),
            Carbon::parse('2026-04-07 00:00:00')
        );

        $types = array_column($out['suggestions'], 'type');

        $this->assertContains('high_value', $types);
    }

    public function test_it_returns_negative_trend_suggestion_when_latest_exceeds_average_by_margin(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => ['hr' => 70],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-02 10:00:00'),
            'encrypted_values' => ['hr' => 72],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-03 10:00:00'),
            'encrypted_values' => ['hr' => 85],
        ]);

        $svc = app(SuggestionService::class);

        $out = $svc->generateForAccount(
            $account->id,
            Carbon::parse('2026-04-01 00:00:00'),
            Carbon::parse('2026-04-07 00:00:00')
        );

        $types = array_column($out['suggestions'], 'type');

        $this->assertContains('negative_trend', $types);
    }

    public function test_it_returns_positive_trend_suggestion_when_latest_is_below_average_by_margin(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => ['weight' => 190],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-02 10:00:00'),
            'encrypted_values' => ['weight' => 191],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-03 10:00:00'),
            'encrypted_values' => ['weight' => 180],
        ]);

        $svc = app(SuggestionService::class);

        $out = $svc->generateForAccount(
            $account->id,
            Carbon::parse('2026-04-01 00:00:00'),
            Carbon::parse('2026-04-07 00:00:00')
        );

        $types = array_column($out['suggestions'], 'type');

        $this->assertContains('positive_trend', $types);
    }

    public function test_it_respects_only_keys_filter(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => [
                'hr' => 90,
                'weight' => 205,
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-02 10:00:00'),
            'encrypted_values' => [
                'hr' => 92,
                'weight' => 207,
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-03 10:00:00'),
            'encrypted_values' => [
                'hr' => 94,
                'weight' => 209,
            ],
        ]);

        $svc = app(SuggestionService::class);

        $out = $svc->generateForAccount(
            $account->id,
            Carbon::parse('2026-04-01 00:00:00'),
            Carbon::parse('2026-04-07 00:00:00'),
            ['hr']
        );

        $metrics = array_filter(array_column($out['suggestions'], 'metric'));

        $this->assertContains('hr', $metrics);
        $this->assertNotContains('weight', $metrics);
    }

    public function test_no_trend_when_delta_equals_margin(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => ['hr' => 70],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-02 10:00:00'),
            'encrypted_values' => ['hr' => 75],
        ]);

        $svc = app(SuggestionService::class);

        $out = $svc->generateForAccount(
            $account->id,
            Carbon::parse('2026-04-01 00:00:00'),
            Carbon::parse('2026-04-07 00:00:00')
        );

        $types = array_column($out['suggestions'], 'type');

        $this->assertNotContains('negative_trend', $types);
        $this->assertNotContains('positive_trend', $types);
    }

    public function test_duplicate_suggestions_are_removed(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => [
                'hr' => 90,
                'weight' => 205,
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-02 10:00:00'),
            'encrypted_values' => [
                'hr' => 92,
                'weight' => 207,
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-03 10:00:00'),
            'encrypted_values' => [
                'hr' => 94,
                'weight' => 209,
            ],
        ]);

        $svc = app(SuggestionService::class);

        $out = $svc->generateForAccount(
            $account->id,
            Carbon::parse('2026-04-01 00:00:00'),
            Carbon::parse('2026-04-07 00:00:00')
        );

        $typeMetricPairs = array_map(
            fn ($suggestion) => $suggestion['type'] . '|' . ($suggestion['metric'] ?? 'global'),
            $out['suggestions']
        );

        $this->assertCount(
            count(array_unique($typeMetricPairs)),
            $typeMetricPairs
        );
    }

    public function test_suggestions_are_sorted_by_severity(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => [
                'hr' => 90,
                'weight' => 150,
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-02 10:00:00'),
            'encrypted_values' => [
                'hr' => 92,
                'weight' => 151,
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-03 10:00:00'),
            'encrypted_values' => [
                'hr' => 94,
                'weight' => 152,
            ],
        ]);

        $svc = app(SuggestionService::class);

        $out = $svc->generateForAccount(
            $account->id,
            Carbon::parse('2026-04-01 00:00:00'),
            Carbon::parse('2026-04-07 00:00:00')
        );

        $severities = array_column($out['suggestions'], 'severity');

        $this->assertNotEmpty($severities);

        $rank = fn (string $severity) => match ($severity) {
            'high' => 3,
            'medium' => 2,
            default => 1,
        };

        for ($i = 0; $i < count($severities) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $rank($severities[$i + 1]),
                $rank($severities[$i])
            );
        }
    }

    public function test_non_numeric_metrics_are_ignored_for_threshold_and_trend_suggestions(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => [
                'hr' => 90,
                'mood' => 'good',
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-02 10:00:00'),
            'encrypted_values' => [
                'hr' => 92,
                'mood' => 'better',
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-03 10:00:00'),
            'encrypted_values' => [
                'hr' => 94,
                'mood' => 'great',
            ],
        ]);

        $svc = app(SuggestionService::class);

        $out = $svc->generateForAccount(
            $account->id,
            Carbon::parse('2026-04-01 00:00:00'),
            Carbon::parse('2026-04-07 00:00:00')
        );

        $moodSuggestions = array_values(array_filter(
            $out['suggestions'],
            fn ($suggestion) => $suggestion['metric'] === 'mood'
        ));

        $moodTypes = array_column($moodSuggestions, 'type');

        $this->assertNotContains('high_value', $moodTypes);
        $this->assertNotContains('negative_trend', $moodTypes);
        $this->assertNotContains('positive_trend', $moodTypes);
    }
}
