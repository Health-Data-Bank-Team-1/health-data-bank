<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if(session()->has('message'))
            <div class="bg-green-100 text-green-800 p-3 rounded text-sm">
                {{ session('message') }}
            </div>
        @endif

        @if(session()->has('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg border overflow-hidden">

            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-semibold">Schema Management</h2>
                <p class="text-sm text-gray-600 mt-1">
                    Apply approved migrations and safely roll back schema changes.
                </p>
            </div>

            <div class="p-6 flex gap-4">
                <button
                    wire:click="runMigrations"
                    onclick="return confirm('Run pending migrations?')"
                    style="background:#2563eb; color:white; padding:8px 14px; border-radius:6px; font-weight:600;"
                >
                    Run Migrations
                </button>

                <button
                    wire:click="rollback"
                    onclick="return confirm('Rollback last migration batch?')"
                    style="background:#dc2626; color:white; padding:8px 14px; border-radius:6px; font-weight:600;"
                >
                    Rollback Last Batch
                </button>
            </div>

            <div class="px-6 pb-6">
                <h3 class="text-sm font-semibold mb-2">Migration Status</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full border">
                        <thead style="background:#f3f4f6;">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs">Ran</th>
                            <th class="px-3 py-2 text-left text-xs">Migration</th>
                            <th class="px-3 py-2 text-left text-xs">Batch</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($migrations as $m)
                            <tr style="border-top:1px solid #e5e7eb;">
                                <td class="px-3 py-2 text-xs">{{ $m['ran'] }}</td>
                                <td class="px-3 py-2 text-xs">{{ $m['migration'] }}</td>
                                <td class="px-3 py-2 text-xs">{{ $m['batch'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if(!empty($output))
                <div class="px-6 pb-6">
                    <h3 class="text-sm font-semibold mb-2">Command Output</h3>
                    <pre style="background:#111827; color:#10b981; padding:10px; border-radius:6px; font-size:12px;">
{{ $output }}
                    </pre>
                </div>
            @endif

        </div>
    </div>
</div>
