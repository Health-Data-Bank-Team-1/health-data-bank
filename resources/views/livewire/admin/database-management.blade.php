<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-3 rounded text-sm">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Create Backup</h2>
                <p class="text-sm text-gray-600 mt-1">
                    Create a manual SQL backup of the current database.
                </p>
            </div>

            <div class="p-6 flex flex-col md:flex-row md:items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" wire:model="compressBackup">
                    Compress backup (.gz)
                </label>

                <button
                    wire:click="createBackup"
                    type="button"
                    class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700"
                >
                    Create Backup
                </button>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Backup Cleanup</h2>
                <p class="text-sm text-gray-600 mt-1">
                    Remove older backups based on retention policy.
                </p>
            </div>

            <div class="p-6 flex flex-col md:flex-row md:items-center gap-4">
                <div>
                    <label for="retentionDays" class="block text-sm font-medium text-gray-700 mb-1">
                        Retention Days
                    </label>
                    <input
                        id="retentionDays"
                        type="number"
                        min="1"
                        wire:model="retentionDays"
                        class="border border-gray-300 rounded px-3 py-2 w-32"
                    >
                </div>

                <button
                    wire:click="cleanupBackups"
                    type="button"
                    class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700"
                >
                    Cleanup Old Backups
                </button>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Backup History</h2>
                <p class="text-sm text-gray-600 mt-1">
                    Review and manage existing backup files.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Filename</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Modified</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Size</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($backups as $backup)
                        <tr class="border-b">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $backup['filename'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $backup['modified'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $backup['size_kb'] }} KB</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        wire:click="restoreBackup('{{ $backup['filename'] }}')"
                                        onclick="return confirm('Restore this backup? This will replace the current database state.')"
                                        type="button"
                                        class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                                    >
                                        Restore
                                    </button>

                                    <button
                                        wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                        onclick="return confirm('Delete this backup file?')"
                                        type="button"
                                        class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-sm text-gray-500">
                                No backups have been created yet.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-900">
            <strong>Warning:</strong>
            Restoring a backup will replace the current database contents with the selected backup file.
            Only perform a restore when you intentionally want to roll the system back to a previous state.
        </div>
    </div>
</div>
