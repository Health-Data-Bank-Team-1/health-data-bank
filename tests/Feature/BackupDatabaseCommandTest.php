<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

class BackupDatabaseCommandTest extends TestCase
{
    /** @test */
    public function backup_database_command_succeeds()
    {
        $this->artisan('backup:database')
             ->assertExitCode(0);
    }

    /** @test */
    public function backup_database_command_with_compress_option()
    {
        $this->artisan('backup:database --compress')
             ->assertExitCode(0);
    }

    /** @test */
    public function backup_database_command_with_cleanup_option()
    {
        $this->artisan('backup:database --cleanup')
             ->assertExitCode(0);
    }

    /** @test */
    public function backup_database_command_displays_success_message()
    {
        $this->artisan('backup:database')
             ->expectsOutput('Starting database backup...')
             ->expectsOutputToContain('✓')
             ->assertExitCode(0);
    }

    /** @test */
    public function backup_database_command_displays_file_size()
    {
        $this->artisan('backup:database')
             ->expectsOutputToContain('MB')
             ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        // Clean up test backups
        $backupPath = storage_path('backups');
        if (File::isDirectory($backupPath)) {
            File::deleteDirectory($backupPath);
        }
        parent::tearDown();
    }
}