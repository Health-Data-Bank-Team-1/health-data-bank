<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-3 rounded text-sm">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Manage User Roles</h2>
                <p class="text-sm text-gray-600 mt-1">
                    Search registered users, filter by current role, and sync their access level.
                </p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by name, email, or role"
                        class="w-full border border-gray-300 rounded px-3 py-2"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Role</label>
                    <select
                        wire:model.live="roleFilter"
                        class="w-full border border-gray-300 rounded px-3 py-2"
                    >
                        <option value="">All Roles</option>
                        @foreach($allowedRoles as $role)
                            <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                        @endforeach
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button
                        wire:click="toggleSortDirection"
                        type="button"
                        class="px-4 py-2 bg-gray-800 text-white rounded-lg"
                    >
                        Sort Role: {{ strtoupper($sortDirection) }}
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                    <tr style="background:#f3f4f6;">
                        <th class="px-4 py-3 text-left text-sm font-semibold border-b">Name</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold border-b">Email</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold border-b">Current Role</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold border-b">Change Role</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($users as $user)
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <td class="px-4 py-3 text-sm">{{ $user['name'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $user['email'] }}</td>

                            <td class="px-4 py-3 text-sm">
                                @if($user['role'] === 'admin')
                                    <span style="background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:6px; font-weight:600;">
                                        admin
                                    </span>
                                @elseif($user['role'] === 'user')
                                    <span style="background:#dbeafe; color:#1d4ed8; padding:4px 8px; border-radius:6px; font-weight:600;">
                                        user
                                    </span>
                                @elseif($user['role'] === 'researcher')
                                    <span style="background:#ede9fe; color:#6d28d9; padding:4px 8px; border-radius:6px; font-weight:600;">
                                        researcher
                                    </span>
                                @elseif($user['role'] === 'provider')
                                    <span style="background:#dcfce7; color:#15803d; padding:4px 8px; border-radius:6px; font-weight:600;">
                                        provider
                                    </span>
                                @else
                                    <span style="background:#f3f4f6; color:#374151; padding:4px 8px; border-radius:6px; font-weight:600;">
                                        {{ $user['role'] }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm">
                                @if($user['role'] === 'admin')
                                    <span style="
                                        background:#fee2e2;
                                        color:#991b1b;
                                        padding:6px 10px;
                                        border-radius:6px;
                                        font-size:12px;
                                        font-weight:600;
                                        display:inline-block;
                                    ">
                                        Admin (locked)
                                    </span>
                                @else
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">

                                        <!-- USER -->
                                        <button
                                            wire:click="syncUserRole('{{ $user['id'] }}', 'user')"
                                            onclick="return confirm('Change this user to the user role?')"
                                            type="button"
                                            style="
                                                background: {{ $user['role'] === 'user' ? '#1d4ed8' : '#3b82f6' }};
                                                color: white;
                                                border: none;
                                                padding: 6px 12px;
                                                border-radius: 6px;
                                                font-size: 12px;
                                                font-weight: 600;
                                                cursor: pointer;
                                            "
                                        >
                                            {{ $user['role'] === 'user' ? '✓ ' : '' }}User
                                        </button>

                                        <!-- RESEARCHER -->
                                        <button
                                            wire:click="syncUserRole('{{ $user['id'] }}', 'researcher')"
                                            onclick="return confirm('Change this user to the researcher role?')"
                                            type="button"
                                            style="
                                                background: {{ $user['role'] === 'researcher' ? '#6d28d9' : '#8b5cf6' }};
                                                color: white;
                                                border: none;
                                                padding: 6px 12px;
                                                border-radius: 6px;
                                                font-size: 12px;
                                                font-weight: 600;
                                                cursor: pointer;
                                            "
                                        >
                                            {{ $user['role'] === 'researcher' ? '✓ ' : '' }}Researcher
                                        </button>

                                        <!-- PROVIDER -->
                                        <button
                                            wire:click="syncUserRole('{{ $user['id'] }}', 'provider')"
                                            onclick="return confirm('Change this user to the provider role?')"
                                            type="button"
                                            style="
                                                background: {{ $user['role'] === 'provider' ? '#15803d' : '#22c55e' }};
                                                color: white;
                                                border: none;
                                                padding: 6px 12px;
                                                border-radius: 6px;
                                                font-size: 12px;
                                                font-weight: 600;
                                                cursor: pointer;
                                            "
                                        >
                                            {{ $user['role'] === 'provider' ? '✓ ' : '' }}Provider
                                        </button>

                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-sm text-gray-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
