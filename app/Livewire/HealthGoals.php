<?php

namespace App\Livewire;

use App\Models\FormField;
use App\Models\HealthGoal;
use App\Services\GoalProgressService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HealthGoals extends Component
{
    public $goals = [];
    public $editingGoalId = null;
    public bool $showForm = false;

    public $metric_key;
    public $comparison_operator;
    public $target_value;
    public $timeframe;
    public $start_date;
    public $end_date;
    public $status = 'ACTIVE';

    public array $metricOptions = [];

    public array $operatorOptions = [
        '<=' => 'At most',
        '>=' => 'At least',
        '='  => 'Exactly',
    ];

    public array $timeframeOptions = [
        'day' => 'Per Day',
        'week' => 'Per Week',
        'month' => 'Per Month',
    ];

    public function mount()
    {
        $this->loadMetricOptions();
        $this->loadGoals();
        $this->resetForm();
    }

    private function loadMetricOptions(): void
    {
        $this->metricOptions = FormField::query()
            ->where('goal_enabled', true)
            ->whereIn('field_type', ['number', 'decimal'])
            ->orderBy('label')
            ->pluck('label', 'metric_key')
            ->toArray();
    }

    private function accountId(): ?string
    {
        $user = Auth::user();

        return DB::table('accounts')
            ->where('email', $user->email)
            ->value('id');
    }

    public function loadGoals(): void
    {
        $accountId = $this->accountId();

        if (!$accountId) {
            $this->goals = [];
            return;
        }

        $progressService = app(GoalProgressService::class);

        $this->goals = HealthGoal::where('account_id', $accountId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (HealthGoal $goal) use ($progressService) {
                return [
                    'goal' => $goal,
                    'progress' => $progressService->calculate($goal),
                ];
            })
            ->toArray();
    }

    public function resetForm(): void
    {
        $this->editingGoalId = null;
        $this->metric_key = array_key_first($this->metricOptions);
        $this->comparison_operator = '<=';
        $this->target_value = 2;
        $this->timeframe = 'week';
        $this->start_date = now()->toDateString();
        $this->end_date = null;
        $this->status = 'ACTIVE';
    }

    public function createGoal(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editGoal(string $goalId): void
    {
        $accountId = $this->accountId();

        abort_unless($accountId, 403, 'Account mapping failed.');

        $goal = HealthGoal::where('account_id', $accountId)
            ->findOrFail($goalId);

        $this->editingGoalId = $goal->id;
        $this->metric_key = $goal->metric_key;
        $this->comparison_operator = $goal->comparison_operator;
        $this->target_value = $goal->target_value;
        $this->timeframe = $goal->timeframe;
        $this->start_date = $goal->start_date?->toDateString() ?? $goal->start_date;
        $this->end_date = $goal->end_date?->toDateString();
        $this->status = $goal->status;

        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate([
            'metric_key' => ['required', 'in:' . implode(',', array_keys($this->metricOptions))],
            'comparison_operator' => ['required', 'in:<=,>=,='],
            'target_value' => ['required', 'numeric', 'min:0'],
            'timeframe' => ['required', 'in:day,week,month'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:ACTIVE,MET,EXPIRED'],
        ]);

        $accountId = $this->accountId();

        abort_unless($accountId, 403, 'Account mapping failed.');

        if ($this->editingGoalId) {
            $goal = HealthGoal::where('account_id', $accountId)
                ->findOrFail($this->editingGoalId);

            $goal->update([
                'metric_key' => $this->metric_key,
                'comparison_operator' => $this->comparison_operator,
                'target_value' => $this->target_value,
                'timeframe' => $this->timeframe,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
            ]);
        } else {
            HealthGoal::create([
                'account_id' => $accountId,
                'metric_key' => $this->metric_key,
                'comparison_operator' => $this->comparison_operator,
                'target_value' => $this->target_value,
                'timeframe' => $this->timeframe,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
            ]);
        }

        $this->loadGoals();
        $this->cancelForm();

        session()->flash('success', 'Health goal saved successfully.');
    }

    public function render()
    {
        return view('livewire.health-goals')
            ->layout('layouts.user');
    }
}
