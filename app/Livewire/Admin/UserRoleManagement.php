<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\AuditLogger;
use Livewire\Component;

class UserRoleManagement extends Component
{
    public string $search = '';
    public string $roleFilter = '';
    public string $sortDirection = 'asc';

    public array $allowedRoles = [
        'user',
        'researcher',
        'provider',
    ];

    public function syncUserRole(string $userId, string $role): void
    {
        abort_unless(in_array($role, $this->allowedRoles, true), 403, 'Invalid role.');

        $user = User::query()->findOrFail($userId);

        // Prevent admins from being changed through this UI.
        if ($user->hasRole('admin')) {
            abort(403, 'Cannot modify admin roles.');
        }

        $previousRoles = $user->getRoleNames()->values()->all();

        $user->syncRoles([$role]);

        AuditLogger::log(
            'admin_user_role_updated',
            ['admin', 'resource:user_role', 'outcome:success'],
            null,
            [],
            [
                'target_user_id' => $user->id,
                'role_before' => implode(',', $previousRoles),
                'role_after' => $role,
            ]
        );

        session()->flash('message', "Updated {$user->email} to role: {$role}");
    }

    public function toggleSortDirection(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function getUsersProperty()
    {
        $users = User::query()
            ->with('roles')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_id' => $user->account_id,
                    'role' => $user->getRoleNames()->first() ?? 'none',
                ];
            });

        if (trim($this->search) !== '') {
            $term = strtolower(trim($this->search));

            $users = $users->filter(function (array $user) use ($term) {
                return str_contains(strtolower($user['name'] ?? ''), $term)
                    || str_contains(strtolower($user['email'] ?? ''), $term)
                    || str_contains(strtolower($user['role'] ?? ''), $term);
            });
        }

        if ($this->roleFilter !== '') {
            $users = $users->filter(fn (array $user) => $user['role'] === $this->roleFilter);
        }

        return $users
            ->sortBy('role', SORT_NATURAL, $this->sortDirection === 'desc')
            ->values();
    }

    public function render()
    {
        return view('livewire.admin.user-role-management', [
            'users' => $this->users,
            'allowedRoles' => $this->allowedRoles,
        ])
            ->layout('layouts.admin')
            ->layoutData([
                'header' => 'User Role Management',
            ]);
    }
}
