<?php

namespace App\Livewire;

use App\Models\ProviderFeedback;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProviderFeedbackList extends Component
{
    public array $feedbackEntries = [];

    public function mount(): void
    {
        $accountId = Auth::user()?->account_id;

        $this->feedbackEntries = ProviderFeedback::query()
            ->where('patient_account_id', $accountId)
            ->with(['provider'])
            ->latest()
            ->get()
            ->map(function (ProviderFeedback $entry) {
                return [
                    'id' => $entry->id,
                    'feedback' => $entry->feedback,
                    'recommended_actions' => $entry->recommended_actions,
                    'provider_name' => $entry->provider?->name ?? 'Provider',
                    'created_at' => optional($entry->created_at)?->toDateTimeString(),
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.provider-feedback-list');
    }
}
