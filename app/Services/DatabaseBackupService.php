<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Exception;

/**
 * DatabaseBackupService
 *
 * Handles database backup generation using mysqldump.
 * Stores backups in the storage/backups directory with timestamped filenames.
 */
class DatabaseBackupService
{
    /**
     * Generate a database backup
     *
     * @param bool $compress Whether to compress the backup file
     * @return array Result array with 'success', 'message', and 'filepath' keys
     */
    public function generateBackup(bool $compress = false): array
    {
        try {
            // Ensure backup directory exists
            $this->ensureBackupDirectoryExists();

            // Get database configuration
            $dbConfig = $this->getDatabaseConfig();

            // Generate filename with timestamp
            $filename = $this->generateBackupFilename($compress);
            $filepath = storage_path('backups/' . $filename);

            // Build and execute mysqldump command
            $command = $this->buildMysqldumpCommand($dbConfig, $filepath, $compress);
            $output = null;
            $exitCode = null;

            exec($command, $output, $exitCode);

            // Check if command executed successfully
            if ($exitCode !== 0) {
                Log::error('Database backup failed', [
                    'exit_code' => $exitCode,
                    'error' => implode(PHP_EOL, $output ?? []),
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to generate database backup. Check logs for details.',
                    'filepath' => null,
                ];
            }

            // Verify file was created
            if (!File::exists($filepath)) {
                return [
                    'success' => false,
                    'message' => 'Backup file was not created.',
                    'filepath' => null,
                ];
            }

            // Log successful backup
            Log::info('Database backup created successfully', [
                'filepath' => $filepath,
                'size' => File::size($filepath),
                'compressed' => $compress,
            ]);

            return [
                'success' => true,
                'message' => 'Database backup generated successfully.',
                'filepath' => $filepath,
            ];
        } catch (Exception $e) {
            Log::error('Database backup exception: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while generating the backup: ' . $e->getMessage(),
                'filepath' => null,
            ];
        }
    }

    /**
     * Get database configuration
     *
     * @return array
     */
    private function getDatabaseConfig(): array
    {
        return [
            'host' => Config::get('database.connections.mysql.host'),
            'port' => Config::get('database.connections.mysql.port', 3306),
            'database' => Config::get('database.connections.mysql.database'),
            'username' => Config::get('database.connections.mysql.username'),
            'password' => Config::get('database.connections.mysql.password'),
        ];
    }

    /**
     * Build the mysqldump command
     *
     * @param array $dbConfig Database configuration
     * @param string $filepath Output file path
     * @param bool $compress Whether to compress output
     * @return string
     */
    private function buildMysqldumpCommand(array $dbConfig, string $filepath, bool $compress = false): string
    {
        $host = escapeshellarg($dbConfig['host']);
        $port = escapeshellarg($dbConfig['port']);
        $database = escapeshellarg($dbConfig['database']);
        $username = escapeshellarg($dbConfig['username']);
        $password = !empty($dbConfig['password']) ? '-p' . escapeshellarg($dbConfig['password']) : '';

        $filepath = escapeshellarg($filepath);

        // Basic mysqldump command with useful options
        $command = "mysqldump --host={$host} --port={$port} --user={$username}";

        if (!empty($password)) {
            $command .= " {$password}";
        }

        $command .= " --single-transaction --quick --lock-tables=false {$database}";

        if ($compress) {
            $command .= " | gzip";
        }

        $command .= " > {$filepath}";

        return $command;
    }

    /**
     * Generate timestamped backup filename
     *
     * @param bool $compress
     * @return string
     */
    private function generateBackupFilename(bool $compress = false): string
    {
        $timestamp = now()->format('Y_m_d_H_i_s');
        $extension = $compress ? '.sql.gz' : '.sql';
        $dbName = Config::get('database.connections.mysql.database');

        return "{$dbName}_backup_{$timestamp}{$extension}";
    }

    /**
     * Ensure backup directory exists
     *
     * @return void
     */
    private function ensureBackupDirectoryExists(): void
    {
        $backupPath = storage_path('backups');

        if (!File::isDirectory($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }
    }

    /**
     * Get list of existing backups
     *
     * @return array
     */
    public function listBackups(): array
    {
        $backupPath = storage_path('backups');

        if (!File::isDirectory($backupPath)) {
            return [];
        }

        $files = File::files($backupPath);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified' => $file->getMTime(),
                'path' => $file->getRealPath(),
            ];
        }

        return $backups;
    }

    /**
     * Delete a backup file
     *
     * @param string $filename
     * @return bool
     */
    public function deleteBackup(string $filename): bool
    {
        $filepath = storage_path('backups/' . $filename);

        // Prevent directory traversal attacks
        if (strpos(realpath($filepath), realpath(storage_path('backups'))) !== 0) {
            Log::warning('Attempted to delete backup outside backups directory', [
                'filename' => $filename,
            ]);
            return false;
        }

        if (File::exists($filepath)) {
            return File::delete($filepath);
        }

        return false;
    }

    /**
     * Clean up old backups based on retention days
     *
     * @param int $retentionDays Number of days to retain backups
     * @return int Number of backups deleted
     */
    public function cleanupOldBackups(int $retentionDays = 30): int
    {
        $backupPath = storage_path('backups');

        if (!File::isDirectory($backupPath)) {
            return 0;
        }

        $files = File::files($backupPath);
        $now = time();
        $deleted = 0;

        foreach ($files as $file) {
            $fileAge = ($now - $file->getMTime()) / 86400; // Convert to days

            if ($fileAge > $retentionDays) {
                if (File::delete($file->getRealPath())) {
                    $deleted++;
                    Log::info('Deleted old backup', [
                        'filename' => $file->getFilename(),
                        'age_days' => round($fileAge),
                    ]);
                }
            }
        }

        return $deleted;
    }
}