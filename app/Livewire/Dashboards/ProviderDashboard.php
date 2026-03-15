<?php

namespace App\Livewire\Dashboards;

use Livewire\Component;
use App\Models\Account;

class ProviderDashboard extends Component
{
    public function mount()
    {
        $user = auth()->user();
        $patients = Account::find($user->account_id)->patients;
        dump($patients);
    }
    public function render()
    {
        return view('dashboards.provider')
            ->layout('layouts.provider');
    }
}
