<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\AuditLogger;
use OwenIt\Auditing\Models\Audit;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $events = Audit::query()
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        $audits = Audit::query()
            ->when($request->filled('event'), function ($query) use ($request) {
                $query->where('event', $request->event);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        AuditLogger::log(
            'audit_log_viewed',
            ['audit', 'outcome:success'],
            auth()->user(),
            [],
            []
        );

        return view('livewire.admin.audit-log', [
            'audits' => $audits,
            'events' => $events,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('admin-access'), 403);

        $validated = $request->validate([
            'event' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'string', 'max:255'],
            'target_type' => ['nullable', 'string', 'max:255'],
            'target_id' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:asc,desc'],
        ]);

        $query = $this->buildAuditQuery($validated);

        AuditLogger::log(
            'audit_log_exported',
            ['audit', 'outcome:success'],
            auth()->user(),
            [],
            [
                'filters' => $validated,
            ]
        );

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'id',
                'event',
                'user_type',
                'user_id',
                'auditable_type',
                'auditable_id',
                'old_values',
                'new_values',
                'url',
                'ip_address',
                'user_agent',
                'tags',
                'created_at',
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->event,
                        $row->user_type,
                        $row->user_id,
                        $row->auditable_type,
                        $row->auditable_id,
                        $row->old_values,
                        $row->new_values,
                        $row->url,
                        $row->ip_address,
                        $row->user_agent,
                        $row->tags,
                        $row->created_at,
                    ]);
                }
            });

            fclose($handle);
        }, 'audit_logs.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function buildAuditQuery(array $validated)
    {
        $auditTable = config('audit.drivers.database.table', 'audits');

        $query = DB::table($auditTable)
            ->leftJoin('accounts', 'accounts.id', '=', $auditTable . '.user_id')
            ->select([
                $auditTable . '.id',
                $auditTable . '.event',
                $auditTable . '.user_type',
                $auditTable . '.user_id',
                $auditTable . '.auditable_type',
                $auditTable . '.auditable_id',
                $auditTable . '.old_values',
                $auditTable . '.new_values',
                $auditTable . '.url',
                $auditTable . '.ip_address',
                $auditTable . '.user_agent',
                $auditTable . '.tags',
                $auditTable . '.created_at',
                'accounts.name',
                'accounts.email',
            ]);

        if (!empty($validated['event'])) {
            $query->where($auditTable . '.event', $validated['event']);
        }

        if (!empty($validated['user_id'])) {
            $search = trim($validated['user_id']);

            $query->where(function ($q) use ($auditTable, $search) {
                $q->where($auditTable . '.user_id', 'like', "%{$search}%")
                    ->orWhere('accounts.name', 'like', "%{$search}%")
                    ->orWhere('accounts.email', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['target_type'])) {
            $query->where($auditTable . '.auditable_type', 'like', '%' . $validated['target_type'] . '%');
        }

        if (!empty($validated['target_id'])) {
            $query->where($auditTable . '.auditable_id', 'like', '%' . $validated['target_id'] . '%');
        }

        if (!empty($validated['tag'])) {
            $query->where($auditTable . '.tags', 'like', '%' . $validated['tag'] . '%');
        }

        if (!empty($validated['from'])) {
            $query->whereDate($auditTable . '.created_at', '>=', $validated['from']);
        }

        if (!empty($validated['to'])) {
            $query->whereDate($auditTable . '.created_at', '<=', $validated['to']);
        }

        $sort = $validated['sort'] ?? 'desc';
        $query->orderBy($auditTable . '.created_at', $sort);

        return $query;
    }
}
