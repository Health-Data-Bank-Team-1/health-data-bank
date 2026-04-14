<?php

namespace App\Livewire\Admin;

use App\Services\AuditLogger;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class SchemaManagement extends Component
{
    public array $migrationStatus = [];
    public string $output = '';

    public function mount(): void
    {
        $this->loadMigrationStatus();
    }

    public function loadMigrationStatus(): void
    {
        Artisan::call('migrate:status');

        $raw = Artisan::output();

        $this->migrationStatus = collect(explode("\n", $raw))
            ->filter(fn ($line) => str_contains($line, '|'))
            ->map(function ($line) {
                $parts = array_map('trim', explode('|', $line));

                return [
                    'ran' => $parts[1] ?? '',
                    'migration' => $parts[2] ?? '',
                    'batch' => $parts[3] ?? '',
                ];
            })
            ->values()
            ->toArray();
    }

    public function runMigrations(): void
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            $this->output = Artisan::output();

            AuditLogger::log(
                'admin_schema_migrate',
                ['admin', 'resource:schema', 'outcome:success'],
                null,
                [],
                []
            );

            session()->flash('message', 'Migrations executed successfully.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->loadMigrationStatus();
    }

    public function rollback(): void
    {
        try {
            Artisan::call('migrate:rollback', [
                '--step' => 1,
                '--force' => true
            ]);

            $this->output = Artisan::output();

            AuditLogger::log(
                'admin_schema_rollback',
                ['admin', 'resource:schema', 'outcome:success'],
                null,
                [],
                []
            );

            session()->flash('message', 'Rollback completed successfully.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->loadMigrationStatus();
    }

    public function render()
    {
        return view('livewire.admin.schema-management', [
            'migrations' => $this->migrationStatus
        ])
            ->layout('layouts.admin')
            ->layoutData([
                'header' => 'Schema Management'
            ]);
    }
}
