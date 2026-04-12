@php
    // Safe, non-sensitive values to display (do NOT show DB password)
    $connection = config('database.default');
    $driver = config("database.connections.$connection.driver");
    $database = config("database.connections.$connection.database");
    $host = config("database.connections.$connection.host");
    $port = config("database.connections.$connection.port");

    // Environment / app metadata
    $appEnv = config('app.env');
    $debug = (bool) config('app.debug');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <div class="space-y-1">
        <p class="text-sm text-gray-600">
            Admin-only database information and maintenance shortcuts. This page intentionally avoids destructive actions.
        </p>
        <p class="text-xs text-gray-500">
            Tip: If you need exports/backups, implement them as queued jobs and log them to the audit log.
        </p>
    </div>

    {{-- Status / Overview --}}
    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-medium text-gray-900">Overview</h2>
                <p class="text-sm text-gray-500">
                    Current application database configuration (non-sensitive).
                </p>
            </div>

            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                {{ $appEnv === 'production' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                Env: {{ strtoupper($appEnv) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Connection</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $connection ?? 'N/A' }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Driver</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $driver ?? 'N/A' }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Debug</p>
                <p class="mt-1 text-sm font-semibold {{ $debug ? 'text-amber-700' : 'text-gray-900' }}">
                    {{ $debug ? 'ON' : 'OFF' }}
                </p>
            </div>
        </div>

        <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Connection details</h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                <div>
                    <dt class="text-gray-500">Host</dt>
                    <dd class="font-medium text-gray-900 break-all">{{ $host ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Port</dt>
                    <dd class="font-medium text-gray-900">{{ $port ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Database</dt>
                    <dd class="font-medium text-gray-900 break-all">{{ $database ?? 'N/A' }}</dd>
                </div>
            </dl>

            <p class="mt-3 text-xs text-gray-500">
                Password/username are not shown for security reasons.
            </p>
        </div>
    </div>

    {{-- Table browser (informational for now) --}}
    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div>
            <h2 class="text-lg font-medium text-gray-900">Table Browser</h2>
            <p class="text-sm text-gray-500">
                Not implemented yet. Recommended next step: load table names from the schema and show counts for a small allowlist.
            </p>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left">
                        <th class="px-6 py-3 font-semibold text-gray-700">Planned feature</th>
                        <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 font-semibold text-gray-700">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900">List tables</td>
                        <td class="px-6 py-4 text-gray-700">Pending</td>
                        <td class="px-6 py-4 text-gray-500">Use schema manager; avoid exposing sensitive tables.</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900">Row counts</td>
                        <td class="px-6 py-4 text-gray-700">Pending</td>
                        <td class="px-6 py-4 text-gray-500">Counts can be expensive; consider caching.</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900">Export to CSV</td>
                        <td class="px-6 py-4 text-gray-700">Pending</td>
                        <td class="px-6 py-4 text-gray-500">Should run as a queued job + audit log entry.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Maintenance actions (safe links/placeholders) --}}
    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div>
            <h2 class="text-lg font-medium text-gray-900">Maintenance</h2>
            <p class="text-sm text-gray-500">
                This project currently does not expose maintenance actions from the UI.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-lg border border-gray-200 p-4 space-y-2">
                <h3 class="text-sm font-semibold text-gray-900">Audit log</h3>
                <p class="text-sm text-gray-600">Review admin activity before adding DB actions.</p>
                <a
                    href="{{ route('admin.audit-log') }}"
                    class="inline-flex justify-center w-full px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700"
                >
                    View Audit Log
                </a>
            </div>

            <div class="rounded-lg border border-gray-200 p-4 space-y-2">
                <h3 class="text-sm font-semibold text-gray-900">Report review</h3>
                <p class="text-sm text-gray-600">Related admin workflow area.</p>
                <a
                    href="{{ route('admin.report-review') }}"
                    class="inline-flex justify-center w-full px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700"
                >
                    Go to Report Review
                </a>
            </div>

            <div class="rounded-lg border border-gray-200 p-4 space-y-2">
                <h3 class="text-sm font-semibold text-gray-900">Form review</h3>
                <p class="text-sm text-gray-600">Manage form templates and approvals.</p>
                <a
                    href="{{ route('admin.forms.index') }}"
                    class="inline-flex justify-center w-full px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700"
                >
                    Go to Form Review
                </a>
            </div>
        </div>

        <p class="text-xs text-gray-500">
            Note: Avoid adding “run SQL” features. If you need operational tools (backup/export), prefer jobs + strict authorization + audit logging.
        </p>
    </div>

</div>