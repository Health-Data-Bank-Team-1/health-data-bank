<?php

namespace App\Livewire;

use App\Services\SuggestionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UserSuggestions extends Component
{
    public $from;

    public $to;

    public array $result = [];

    public function mount(SuggestionService $suggestionService): void
    {
        $this->from = now()->subDays(30)->toDateString();
        $this->to = now()->toDateString();

        $this->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
        ]);

        $user = Auth::user();
        $accountId = $user->account_id;

        $this->result = $suggestionService->generateForAccount(
            $accountId,
            Carbon::parse($this->from),
            Carbon::parse($this->to),
        );
    }

    public function render()
    {
        return view('livewire.user-suggestions')
            ->layout('layouts.user')
            ->layoutData([
                'header' => 'Suggestions',
            ]);
    }
}
