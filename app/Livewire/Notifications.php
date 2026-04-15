<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;

    public $showModal = false;

    public $selectedNotification;

    public function open(Notification $notification)
    {
        $user = auth()->user();

        if ($notification->account_id !== $user->account_id) {
            abort(403);
        }

        $notification->update(['status' => 'read']);

        if (! empty($notification->link)) {
            return redirect()->to($notification->link);
        }

        $this->selectedNotification = $notification;
        $this->showModal = true;
    }

    public function render()
    {
        $user = auth()->user();

        $notifications = Notification::query()
            ->where('account_id', $user->account_id)
            ->orderByRaw("FIELD(status, 'unread', 'read')")
            ->latest()
            ->paginate(15);

        return view('livewire.notifications', [
            'notifications' => $notifications,
        ])->layout('layouts.user')->layoutData([
            'header' => __('Notifications'),
        ]);
    }
}
