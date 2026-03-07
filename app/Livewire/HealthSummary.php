<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\PersonalSummaryService;
use Carbon\Carbon;

class HealthSummary extends Component
{
    public $from;
    public $to;
    public $summary = [];
    public $avgs = [];

    public function loadSummary(PersonalSummaryService $svc)
    {
        $this->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
        ]);

        $user = auth()->user();

        if (!$user?->account_id) {
            $this->addError('account', 'User has no account attached.');
            return;
        }

        $this->summary = $svc->summaryForAccount(
            $user->account_id,
            Carbon::parse($this->from),
            Carbon::parse($this->to),
        );

        $this->avgs = $this->summary["averages"];
    }
    public function render()
    {
        return view('livewire.health-summary')
            ->layout('layouts.user')
            ->layoutData([
                'header' => 'Health Summary'
            ]);
    }
}
