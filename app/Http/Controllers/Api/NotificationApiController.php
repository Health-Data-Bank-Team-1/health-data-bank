<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Notification::query()
            ->where('account_id', $user->account_id)
            ->latest();

        // Optional filter: /api/notifications?status=unread
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        // Optional filter: /api/notifications?type=reminder
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        // Pagination (default 20, max 100)
        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);

        return response()->json($query->paginate($perPage));
    }

    public function show(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        if ($notification->account_id !== $user->account_id) {
            abort(403);
        }

        return response()->json($notification);
    }

    public function update(Request $request, Notification $notification): JsonResponse
    {
        $user = Auth::user();

        if ($notification->account_id !== $user->account_id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:unread,read'],
        ]);

        $notification->update($validated);

        return response()->json($notification);
    }

    public function markRead(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        if ($notification->account_id !== $user->account_id) {
            abort(403);
        }

        $notification->update(['status' => 'read']);

        return response()->json([
            'ok' => true,
            'notification' => $notification->fresh(),
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        $user = Auth::user();

        $count = Notification::query()
            ->where('account_id', $user->account_id)
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        return response()->json([
            'ok' => true,
            'updated' => $count,
        ]);
    }
}