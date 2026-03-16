<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_if(Gate::denies('admin-access'), 403);

        $validated = $request->validate([
            'event' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'uuid'],
            'auditable_type' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $this->buildAuditQuery($validated);

        $perPage = $validated['per_page'] ?? 15;

        return response()->json($query->paginate($perPage));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('admin-access'), 403);

        $validated = $request->validate([
            'event' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'uuid'],
            'auditable_type' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $query = $this->buildAuditQuery($validated);

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
            ->select([
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
            ])
            ->orderByDesc('created_at');

        if (!empty($validated['event'])) {
            $query->where('event', $validated['event']);
        }

        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (!empty($validated['auditable_type'])) {
            $query->where('auditable_type', $validated['auditable_type']);
        }

        if (!empty($validated['tag'])) {
            $query->where('tags', 'like', '%' . $validated['tag'] . '%');
        }

        if (!empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if (!empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        return $query;
    }
}
