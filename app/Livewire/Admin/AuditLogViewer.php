<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    public int $perPage = 15;

    public ?string $event = null;
    public ?string $tag = null;
    public ?string $userId = null;
    public ?string $from = null;
    public ?string $to = null;

    public function mount(): void
    {
        abort_if(Gate::denies('admin-access'), 403);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingEvent() { $this->resetPage(); }
    public function updatingTag() { $this->resetPage(); }
    public function updatingUserId() { $this->resetPage(); }
    public function updatingFrom() { $this->resetPage(); }
    public function updatingTo() { $this->resetPage(); }

    public function render()
    {
        $auditTable = config('audit.drivers.database.table', 'audits');

        $query = DB::table($auditTable)
            ->select([
                'id',
                'event',
                'user_id',
                'auditable_type',
                'auditable_id',
                'url',
                'ip_address',
                'tags',
                'created_at',
            ])
            ->orderByDesc('created_at');

        if ($this->event) {
            $query->where('event', $this->event);
        }

        if ($this->tag) {
            $query->where('tags', 'like', "%{$this->tag}%");
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }

        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }

        $audits = $query->paginate($this->perPage);

        return view('livewire.admin.audit-log-viewer', [
            'audits' => $audits,
        ]);
    }

    public function exportCsv()
    {
        $params = http_build_query([
            'event' => $this->event,
            'tag' => $this->tag,
            'user_id' => $this->userId,
            'from' => $this->from,
            'to' => $this->to,
        ]);

        return redirect("/api/admin/audits/export.csv?$params");
    }
}
