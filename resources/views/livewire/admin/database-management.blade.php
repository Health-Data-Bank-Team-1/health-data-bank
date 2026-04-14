{{-- resources/views/livewire/admin/database-management.blade.php --}}

@php
    $connection = $dbInfo['connection'] ?? null;
    $driver = $dbInfo['driver'] ?? null;
    $database = $dbInfo['database'] ?? null;
    $host = $dbInfo['host'] ?? null;
    $port = $dbInfo['port'] ?? null;
    $appEnv = $dbInfo['appEnv'] ?? null;
    $debug = (bool) ($dbInfo['debug'] ?? false);

    $pagination = $preview['pagination'] ?? null;
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
                Env: {{ strtoupper((string) $appEnv) }}
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

    {{-- Table browser (functional) --}}
    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div>
            <h2 class="text-lg font-medium text-gray-900">Table Browser</h2>
            <p class="text-sm text-gray-500">
                Read-only: lists MySQL tables and provides a preview. Sensitive-ish columns are masked.
            </p>

            @if(!empty($tableListError))
                <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    Failed to load tables: {{ $tableListError }}
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Table list --}}
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left">
                            <th class="px-6 py-3 font-semibold text-gray-700">Table</th>
                            <th class="px-6 py-3 font-semibold text-gray-700">Rows</th>
                            <th class="px-6 py-3 font-semibold text-gray-700">Preview</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($tables as $t)
                            <tr class="{{ $selectedTable === $t['name'] ? 'bg-indigo-50' : '' }}">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $t['name'] }}
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    @if(is_int($t['count']))
                                        {{ number_format($t['count']) }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <a
                                        href="{{ route('admin.database-management', ['table' => $t['name'], 'per_page' => $perPage]) }}"
                                        class="inline-flex justify-center w-full px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700"
                                    >
                                        Preview
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-4 text-gray-500" colspan="3">
                                    No tables found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Preview panel --}}
            <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Preview</h3>
                        <p class="text-xs text-gray-500">
                            Uses query params (<code class="text-gray-700">table</code>, <code class="text-gray-700">page</code>, <code class="text-gray-700">per_page</code>).
                        </p>
                    </div>

                    <form method="GET" action="{{ route('admin.database-management') }}" class="flex items-center gap-2">
                        @if($selectedTable)
                            <input type="hidden" name="table" value="{{ $selectedTable }}">
                        @endif

                        <label class="text-xs text-gray-600">
                            Per page
                            <select name="per_page" class="ml-1 rounded border-gray-300 text-xs">
                                @foreach([10,25,50,100] as $n)
                                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </label>

                        <button class="px-2 py-1 rounded bg-white border border-gray-300 text-xs hover:bg-gray-100">
                            Apply
                        </button>
                    </form>
                </div>

                @if(!$selectedTable)
                    <div class="mt-4 text-sm text-gray-600">
                        Select a table from the left to preview its rows.
                    </div>
                @else
                    @if(($preview['error'] ?? null))
                        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            Preview error: {{ $preview['error'] }}
                        </div>
                    @else
                        <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200 bg-white">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @foreach(($preview['columns'] ?? []) as $col)
                                            <th class="px-3 py-2 text-left font-semibold text-gray-700 whitespace-nowrap">
                                                {{ $col }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse(($preview['rows'] ?? []) as $row)
                                        <tr>
                                            @foreach(($preview['columns'] ?? []) as $col)
                                                <td class="px-3 py-2 text-gray-700 whitespace-nowrap">
                                                    {{ $row[$col] ?? '' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-3 py-4 text-gray-500" colspan="{{ max(1, count($preview['columns'] ?? [])) }}">
                                                No rows found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($pagination)
                            @php
                                $base = ['table' => $selectedTable, 'per_page' => $perPage];
                                $page = (int) ($pagination['page'] ?? 1);
                            @endphp

                            <div class="mt-3 flex items-center justify-between">
                                <div class="text-xs text-gray-600">
                                    Page {{ $page }}
                                    @if(isset($pagination['total']) && is_int($pagination['total']))
                                        <span class="ml-2 text-gray-500">({{ number_format($pagination['total']) }} rows)</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    @if(($pagination['has_prev'] ?? false))
                                        <a class="px-2 py-1 rounded bg-white border border-gray-300 text-xs hover:bg-gray-100"
                                           href="{{ route('admin.database-management', array_merge($base, ['page' => $page - 1])) }}">
                                            Prev
                                        </a>
                                    @else
                                        <span class="px-2 py-1 rounded bg-gray-100 border border-gray-200 text-xs text-gray-400">Prev</span>
                                    @endif

                                    @if(($pagination['has_next'] ?? false))
                                        <a class="px-2 py-1 rounded bg-white border border-gray-300 text-xs hover:bg-gray-100"
                                           href="{{ route('admin.database-management', array_merge($base, ['page' => $page + 1])) }}">
                                            Next
                                        </a>
                                    @else
                                        <span class="px-2 py-1 rounded bg-gray-100 border border-gray-200 text-xs text-gray-400">Next</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <p class="mt-3 text-xs text-gray-500">
                            Note: Avoid adding “run SQL” features. If you need exports/backups, prefer queued jobs + strict authorization + audit logging.
                        </p>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Maintenance actions --}}
    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div>
            <h2 class="text-lg font-medium text-gray-900">Maintenance</h2>
            <p class="text-sm text-gray-500">
                This project currently does not expose destructive maintenance actions from the UI.
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
    </div>

</div>
