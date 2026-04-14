<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use OwenIt\Auditing\Models\Audit;

class AuditLog extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public int $perPage = 15;

    public string $presetRange = '72h';
    public string $sortDirection = 'desc';

    public ?string $event = null;
    public ?string $tag = null;
    public ?string $userId = null;
    public ?string $from = null;
    public ?string $to = null;
    public ?string $targetType = null;
    public ?string $targetId = null;

    public function mount(): void
    {
        abort_if(Gate::denies('admin-access'), 403);

        $this->applyPresetRange();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingPresetRange(): void { $this->resetPage(); }
    public function updatingSortDirection(): void { $this->resetPage(); }
    public function updatingEvent(): void { $this->resetPage(); }
    public function updatingTag(): void { $this->resetPage(); }
    public function updatingUserId(): void { $this->resetPage(); }
    public function updatingFrom(): void { $this->resetPage(); }
    public function updatingTo(): void { $this->resetPage(); }
    public function updatingTargetType(): void { $this->resetPage(); }
    public function updatingTargetId(): void { $this->resetPage(); }

    public function updatedPresetRange($value): void
    {
        if ($value !== 'custom') {
            $this->applyPresetRange();
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->event = null;
        $this->tag = null;
        $this->userId = null;
        $this->targetType = null;
        $this->targetId = null;
        $this->sortDirection = 'desc';
        $this->presetRange = '72h';

        $this->applyPresetRange();
        $this->resetPage();
    }

    protected function applyPresetRange(): void
    {
        if ($this->presetRange === '24h') {
            $this->from = now()->subDay()->toDateString();
            $this->to = now()->toDateString();
        } elseif ($this->presetRange === '72h') {
            $this->from = now()->subDays(3)->toDateString();
            $this->to = now()->toDateString();
        } elseif ($this->presetRange === '7d') {
            $this->from = now()->subDays(7)->toDateString();
            $this->to = now()->toDateString();
        }
    }

    protected function baseQuery()
    {
        $auditTable = config('audit.drivers.database.table', 'audits');

        $query = DB::table($auditTable)
            ->leftJoin('accounts', 'accounts.id', '=', $auditTable . '.user_id')
            ->select([
                $auditTable . '.id',
                $auditTable . '.event',
                $auditTable . '.user_id',
                $auditTable . '.auditable_type',
                $auditTable . '.auditable_id',
                $auditTable . '.url',
                $auditTable . '.ip_address',
                $auditTable . '.tags',
                $auditTable . '.created_at',

                'accounts.name',
                'accounts.email',
            ]);

        if ($this->event) {
            $query->where($auditTable . '.event', $this->event);
        }

        if ($this->tag) {
            $query->where($auditTable . '.tags', 'like', "%{$this->tag}%");
        }

        if ($this->userId) {
            $search = trim($this->userId);

            $query->where(function ($q) use ($auditTable, $search) {
                $q->where($auditTable . '.user_id', 'like', "%{$search}%")
                    ->orWhere('accounts.name', 'like', "%{$search}%")
                    ->orWhere('accounts.email', 'like', "%{$search}%");
            });
        }

        if ($this->targetType) {
            $query->where($auditTable . '.auditable_type', 'like', "%{$this->targetType}%");
        }

        if ($this->targetId) {
            $query->where($auditTable . '.auditable_id', 'like', "%{$this->targetId}%");
        }

        if ($this->from) {
            $query->whereDate($auditTable . '.created_at', '>=', $this->from);
        }

        if ($this->to) {
            $query->whereDate($auditTable . '.created_at', '<=', $this->to);
        }

        return $query;
    }

    protected function summaryCount(?string $event = null): int
    {
        $query = $this->baseQuery();

        if ($event) {
            $query->where('event', $event);
        }

        return $query->count();
    }

    public function render()
    {
        $audits = $this->baseQuery()
            ->orderBy('created_at', $this->sortDirection)
            ->paginate($this->perPage);

        $events = Audit::query()
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        return view('livewire.admin.audit-log', [
            'audits' => $audits,
            'totalEvents' => $this->summaryCount(),
            'events' => $events,
        ])
            ->layout('layouts.admin')
            ->layoutData([
                'header' => 'Audit Log',
            ]);
    }
    public function exportCsv()
    {
        return redirect()->route('admin.audit-log.export', [
            'preset_range' => $this->presetRange,
            'event' => $this->event,
            'tag' => $this->tag,
            'user_id' => $this->userId,
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'from' => $this->from,
            'to' => $this->to,
            'sort' => $this->sortDirection,
        ]);
    }
}
