<?php

namespace App\Livewire;

use App\Models\HealthGoal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HealthGoals extends Component
{
    public $goals = [];
    public $editingGoalId = null;
    public bool $showForm = false;

    public $metric_key = 'alcohol_consumption';
    public $comparison_operator = '<=';
    public $target_value = 2;
    public $timeframe = 'week';
    public $start_date;
    public $end_date;
    public $status = 'ACTIVE';

    public array $metricOptions = [
        'alcohol_consumption' => 'Alcohol Consumption',
        'sleep_hours' => 'Sleep Hours',
        'stress_level' => 'Stress Level',
        'exercise_frequency' => 'Exercise Frequency',
    ];

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
        $this->loadGoals();
        $this->resetForm();
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

        $this->goals = HealthGoal::where('account_id', $accountId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function resetForm(): void
    {
        $this->editingGoalId = null;
        $this->metric_key = 'alcohol_consumption';
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
        $goal = HealthGoal::findOrFail($goalId);

        $this->editingGoalId = $goal->id;
        $this->metric_key = $goal->metric_key;
        $this->comparison_operator = $goal->comparison_operator;
        $this->target_value = $goal->target_value;
        $this->timeframe = $goal->timeframe;
        $this->start_date = $goal->start_date;
        $this->end_date = $goal->end_date;
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
            'metric_key' => ['required', 'string'],
            'comparison_operator' => ['required', 'in:<=,>=,='],
            'target_value' => ['required', 'integer', 'min:0'],
            'timeframe' => ['required', 'in:day,week,month'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:ACTIVE,MET,EXPIRED'],
        ]);

        $accountId = $this->accountId();

        abort_unless($accountId, 403, 'Account mapping failed.');

        if ($this->editingGoalId) {
            $goal = HealthGoal::findOrFail($this->editingGoalId);

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
            ->layout('layouts.app');
    }
}
