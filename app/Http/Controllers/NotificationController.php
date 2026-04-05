<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $notifications = Notification::query()
            ->where('account_id', $user->account_id)
            ->latest()
            ->get();

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function open(Notification $notification)
    {
        $user = Auth::user();

        // security check
        if ($notification->account_id !== $user->account_id) {
            abort(403);
        }

        // mark as read
        $notification->update([
            'status' => 'read',
        ]);

        // redirect to link (fallback if missing)
        return redirect($notification->link ?? '/user/dashboard');
    }
}
