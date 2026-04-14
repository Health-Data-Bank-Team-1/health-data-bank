<?php

namespace App\Livewire\Admin;

use App\Services\AuditLogger;
use App\Services\DatabaseBackupService;
use Livewire\Component;

class DatabaseManagement extends Component
{
    public bool $compressBackup = false;
    public int $retentionDays = 30;

    public array $backups = [];

    public function mount(DatabaseBackupService $backupService): void
    {
        $this->loadBackups($backupService);
    }

    public function createBackup(DatabaseBackupService $backupService): void
    {
        $result = $backupService->generateBackup($this->compressBackup);

        if ($result['success']) {
            AuditLogger::log(
                'admin_database_backup_created',
                ['admin', 'resource:database_backup', 'outcome:success'],
                null,
                [],
                [
                    'backup_location' => $result['filepath'],
                    'compressed' => $this->compressBackup,
                ]
            );

            session()->flash('message', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }

        $this->loadBackups($backupService);
    }

    public function deleteBackup(string $filename, DatabaseBackupService $backupService): void
    {
        $deleted = $backupService->deleteBackup($filename);

        if ($deleted) {
            AuditLogger::log(
                'admin_database_backup_deleted',
                ['admin', 'resource:database_backup', 'outcome:success'],
                null,
                [],
                [
                    'backup_ref' => $filename,
                ]
            );

            session()->flash('message', 'Backup deleted successfully.');
        } else {
            session()->flash('error', 'Unable to delete backup.');
        }

        $this->loadBackups($backupService);
    }

    public function cleanupBackups(DatabaseBackupService $backupService): void
    {
        $deletedCount = $backupService->cleanupOldBackups($this->retentionDays);

        AuditLogger::log(
            'admin_database_backup_cleanup',
            ['admin', 'resource:database_backup', 'outcome:success'],
            null,
            [],
            [
                'retention_days' => $this->retentionDays,
                'deleted_count' => $deletedCount,
            ]
        );

        session()->flash('message', "Cleanup complete. Deleted {$deletedCount} old backup(s).");

        $this->loadBackups($backupService);
    }

    public function restoreBackup(string $filename, DatabaseBackupService $backupService): void
    {
        $result = $backupService->restoreBackup($filename);

        if ($result['success']) {
            AuditLogger::log(
                'admin_database_backup_restored',
                ['admin', 'resource:database_backup', 'outcome:success'],
                null,
                [],
                [
                    'backup_ref' => $filename,
                    'backup_location' => $result['filepath'],
                ]
            );

            session()->flash('message', $result['message']);
        } else {
            AuditLogger::log(
                'admin_database_backup_restore_failed',
                ['admin', 'resource:database_backup', 'outcome:failure'],
                null,
                [],
                [
                    'backup_ref' => $filename,
                    'backup_location' => $result['filepath'],
                    'error' => $result['message'],
                ]
            );

            session()->flash('error', $result['message']);
        }

        $this->loadBackups($backupService);
    }

    protected function loadBackups(DatabaseBackupService $backupService): void
    {
        $this->backups = collect($backupService->listBackups())
            ->sortByDesc('modified')
            ->values()
            ->map(function (array $backup) {
                return [
                    'filename' => $backup['filename'],
                    'size_kb' => round(($backup['size'] ?? 0) / 1024, 1),
                    'modified' => !empty($backup['modified'])
                        ? date('Y-m-d H:i:s', $backup['modified'])
                        : null,
                    'path' => $backup['path'] ?? null,
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.database-management', [
            'backups' => $this->backups,
        ])
            ->layout('layouts.admin')
            ->layoutData([
                'header' => 'Database Management',
            ]);
    }
}
