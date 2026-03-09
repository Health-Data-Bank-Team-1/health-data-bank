<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\PersonalSummaryService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MeSummaryController extends Controller
{
    public function show(Request $request, PersonalSummaryService $svc)
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
            'keys' => ['sometimes', 'string'], //comma-separated: hr,weight,etc
        ]);

        $user = $request->user();
        abort_unless($user?->account_id, 422, 'User has no account attached.');

        $keys = [];
        if ($request->filled('keys')) {
            $keys = array_values(array_filter(array_map('trim', explode(',', $request->keys))));
        }

        AuditLogger::log(
            'reporting_summary_view',
            ['reporting', 'resource:summary'],
            null,
            [],
            [
                'from' => $request->from,
                'to' => $request->to,
                'keys' => $keys, //list of metric keys only
            ]
        );

        return response()->json(
            $svc->summaryForAccount(
                $user->account_id,
                Carbon::parse($request->from),
                Carbon::parse($request->to),
                $keys
            )
        );
    }
}
