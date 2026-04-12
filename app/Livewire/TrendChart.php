<?php

namespace App\Livewire;

use App\Models\HealthEntry;
use App\Services\AuditLogger;
use App\Services\HealthMetricRegistry;
use App\Services\TrendCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TrendChart extends Component
{
    public string $chartId;

    public string $curr_metric = '';

    public array $available_metrics = [];

    public string $groupBy = 'day';

    public array $chartLabels = [];

    public array $chartValues = [];

    public string $chartLabel = '';

    private HealthMetricRegistry $registry;

    public function boot(HealthMetricRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function mount(string $groupBy = 'day')
    {
        $this->chartId = 'trendChart_'.uniqid();
        $this->groupBy = $groupBy;
        $this->available_metrics = $this->resolveAvailableMetrics();
        $this->curr_metric = array_key_exists('submission_count', $this->available_metrics)
            ? 'submission_count'
            : (array_key_first($this->available_metrics) ?? '');
        $this->loadChartData();
    }

    public function updatedCurrMetric(): void
    {
        $this->loadChartData();
    }

    public function updatedGroupBy(): void
    {
        $this->loadChartData();
    }

    public function loadChartData(): void
    {
        if (empty($this->curr_metric)) {
            $this->chartLabels = [];
            $this->chartValues = [];
            $this->chartLabel = '';

            return;
        }

        $user = auth()->user();
        $accountId = $user?->account_id;

        if (! $accountId) {
            $this->chartLabels = [];
            $this->chartValues = [];

            return;
        }

        AuditLogger::log(
            'dashboard_trends_view_requested',
            ['reporting', 'resource:dashboard_trends', 'outcome:success'],
            null,
            [],
            [
                'metric' => $this->curr_metric,
                'group_by' => $this->groupBy,
            ]
        );

        $from = now()->subDays(90);
        $to = now();

        if ($this->curr_metric === 'submission_count') {
            $this->loadSubmissionCount($accountId, $from, $to);
        } else {
            $result = app(TrendCalculationService::class)->trendForAccount(
                $accountId,
                $this->curr_metric,
                CarbonImmutable::parse($from),
                CarbonImmutable::parse($to),
                $this->groupBy
            );

            $this->chartLabel = $this->registry->labelFor($this->curr_metric) ?? $this->curr_metric;

            $points = collect($result['points'] ?? []);
            $this->chartLabels = $points->pluck('bucket_start')->map(fn ($d) => CarbonImmutable::parse($d)->format('M j, Y'))->values()->toArray();
            $this->chartValues = $points->pluck('avg')->values()->map(fn ($v) => $v !== null ? round($v, 2) : null)->toArray();
        }

        $this->dispatch('chart-updated');
    }

    public function render()
    {
        return view('livewire.trend-chart');
    }

    private function loadSubmissionCount(string $accountId, $from, $to): void
    {
        $this->chartLabel = 'Submission Count';

        $periodExpr = match ($this->groupBy) {
            'month' => "DATE_FORMAT(submitted_at, '%Y-%m')",
            'week' => "CONCAT(LEFT(YEARWEEK(submitted_at, 3), 4), '-W', RIGHT(YEARWEEK(submitted_at, 3), 2))",
            default => 'DATE(submitted_at)',
        };

        $rows = DB::table('form_submissions')
            ->where('account_id', $accountId)
            ->whereBetween('submitted_at', [$from, $to])
            ->select([
                DB::raw("$periodExpr as period"),
                DB::raw('COUNT(*) as value'),
            ])
            ->groupBy(DB::raw($periodExpr))
            ->orderBy(DB::raw($periodExpr))
            ->get();

        $this->chartLabels = $rows->pluck('period')->values()->toArray();
        $this->chartValues = $rows->pluck('value')->values()->toArray();
    }

    private function resolveAvailableMetrics(): array
    {
        $user = auth()->user();
        $accountId = $user?->account_id;

        if (! $accountId) {
            return [];
        }

        $numericKeys = collect($this->registry->all())
            ->filter(fn ($cfg) => ($cfg['type'] ?? '') === 'number')
            ->keys()
            ->toArray();

        $usedKeys = HealthEntry::query()
            ->where('account_id', $accountId)
            ->limit(200)
            ->pluck('encrypted_values')
            ->filter()
            ->flatMap(fn ($vals) => is_array($vals) ? array_keys($vals) : [])
            ->unique()
            ->filter(fn ($key) => in_array($key, $numericKeys, true))
            ->values()
            ->toArray();

        $metrics = ['submission_count' => 'Submission Count'];
        foreach ($usedKeys as $key) {
            $metrics[$key] = $this->registry->labelFor($key) ?? $key;
        }

        return $metrics;
    }
}
