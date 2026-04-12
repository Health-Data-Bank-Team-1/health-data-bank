<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database
                            {--compress : Compress the backup file using gzip}
                            {--cleanup : Run cleanup of old backups after creating new backup}
                            {--retention=30 : Number of days to retain backups (used with --cleanup)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a backup of the Health Data Bank database';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info('Starting database backup...');

        // Generate the backup
        $compress = $this->option('compress');
        $result = $backupService->generateBackup($compress);

        if ($result['success']) {
            $this->info('✓ ' . $result['message']);
            $this->line('Backup location: ' . $result['filepath']);

            // Get file size
            if (file_exists($result['filepath'])) {
                $sizeInMB = filesize($result['filepath']) / (1024 * 1024);
                $this->line('Backup size: ' . round($sizeInMB, 2) . ' MB');
            }

            // Optionally cleanup old backups
            if ($this->option('cleanup')) {
                $this->line('');
                $this->info('Running cleanup of old backups...');
                $retentionDays = (int) $this->option('retention');
                $deleted = $backupService->cleanupOldBackups($retentionDays);
                $this->info("Deleted {$deleted} backup(s) older than {$retentionDays} days.");
            }

            return self::SUCCESS;
        } else {
            $this->error('✗ ' . $result['message']);
            return self::FAILURE;
        }
    }
}