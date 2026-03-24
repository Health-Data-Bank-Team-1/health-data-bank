<?php

namespace App\Livewire;

use App\Models\HealthGoal;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MyProgress extends Component
{
    public $goals = [];
    public array $goalProgress = [];

    public function mount(): void
    {
        $user = Auth::user();

        $accountId = DB::table('accounts')
            ->where('email', $user->email)
            ->value('id');

        if (!$accountId) {
            return;
        }

        $this->goals = HealthGoal::where('account_id', $accountId)
            ->orderByDesc('created_at')
            ->get();

        foreach ($this->goals as $goal) {
            $endDate = $goal->end_date ?? now()->toDateString();

            $progressCount = DB::table('form_submissions')
                ->where('account_id', $accountId)
                ->whereBetween(DB::raw('DATE(submitted_at)'), [
                    $goal->start_date,
                    $endDate
                ])
                ->count();

            $groupAverage = (float) DB::table('form_submissions')
                ->whereBetween(DB::raw('DATE(submitted_at)'), [
                    $goal->start_date,
                    $endDate
                ])
                ->selectRaw('COUNT(*) / NULLIF(COUNT(DISTINCT account_id), 0) as avg_count')
                ->value('avg_count');

            $this->goalProgress[$goal->id] = [
                'current' => $progressCount,
                'group_average' => $groupAverage,
            ];
        }
    }

    public function goalSummary($goal): string
    {
        $metric = ucfirst(str_replace('_', ' ', $goal->metric_key));

        $operatorText = match ($goal->comparison_operator) {
            '<=' => 'maximum',
            '>=' => 'minimum',
            '='  => 'exactly',
            default => $goal->comparison_operator,
        };

        return "{$metric} — {$operatorText} {$goal->target_value} per {$goal->timeframe}";
    }

    public function render()
    {
        return view('livewire.my-progress')
            ->layout('layouts.user');
    }
}
