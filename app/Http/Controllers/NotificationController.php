<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $accountId = Auth::user()->account_id;

        $notifications = Notification::query()
            ->where('account_id', $accountId)
            ->latest()
            ->get();

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }
}
