<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * DatabaseBackupService
 *
 * Handles database backup generation and restoration using mysqldump/mysql.
 * Stores backups in the storage/backups directory with timestamped filenames.
 */
class DatabaseBackupService
{
    /**
     * Generate a database backup.
     *
     * @param bool $compress Whether to compress the backup file
     * @return array{success:bool,message:string,filepath:?string}
     */
    public function generateBackup(bool $compress = false): array
    {
        try {
            $this->ensureBackupDirectoryExists();

            $dbConfig = $this->getDatabaseConfig();

            $tempSqlPath = storage_path('backups/temp_' . uniqid('', true) . '.sql');

            $command = $this->buildMysqldumpCommand($dbConfig, $tempSqlPath);

            Log::info('Database backup command prepared', [
                'backup_path' => $tempSqlPath,
                'command' => $command,
                'running_user' => trim((string) shell_exec('whoami')),
            ]);

            $output = [];
            $exitCode = 0;

            exec($command, $output, $exitCode);

            Log::info('Database backup command result', [
                'exit_code' => $exitCode,
                'output' => $output,
                'running_user' => trim((string) shell_exec('whoami')),
            ]);

            if ($exitCode !== 0) {
                Log::error('Database backup failed', [
                    'exit_code' => $exitCode,
                    'error' => implode(PHP_EOL, $output),
                    'compress' => $compress,
                ]);

                if (File::exists($tempSqlPath)) {
                    File::delete($tempSqlPath);
                }

                return [
                    'success' => false,
                    'message' => 'Failed to generate database backup. Check logs for details.',
                    'filepath' => null,
                ];
            }

            if (!File::exists($tempSqlPath)) {
                Log::error('Database backup file was not created', [
                    'backup_path' => $tempSqlPath,
                    'compress' => $compress,
                ]);

                return [
                    'success' => false,
                    'message' => 'Backup file was not created.',
                    'filepath' => null,
                ];
            }

            $tempSize = File::size($tempSqlPath);

            if ($tempSize <= 0) {
                Log::error('Database backup file was empty', [
                    'backup_path' => $tempSqlPath,
                    'compress' => $compress,
                ]);

                File::delete($tempSqlPath);

                return [
                    'success' => false,
                    'message' => 'Backup file was empty.',
                    'filepath' => null,
                ];
            }

            if ($compress) {
                $filepath = storage_path('backups/' . $this->generateBackupFilename(true));

                $sqlContents = File::get($tempSqlPath);
                $gz = gzopen($filepath, 'w9');

                if ($gz === false) {
                    File::delete($tempSqlPath);

                    Log::error('Failed to open gzip backup file for writing', [
                        'backup_path' => $filepath,
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Failed to compress backup file.',
                        'filepath' => null,
                    ];
                }

                gzwrite($gz, $sqlContents);
                gzclose($gz);

                File::delete($tempSqlPath);
            } else {
                $filepath = storage_path('backups/' . $this->generateBackupFilename(false));
                File::move($tempSqlPath, $filepath);
            }

            if (!File::exists($filepath)) {
                Log::error('Final database backup file was not created', [
                    'backup_path' => $filepath,
                    'compress' => $compress,
                ]);

                return [
                    'success' => false,
                    'message' => 'Final backup file was not created.',
                    'filepath' => null,
                ];
            }

            $finalSize = File::size($filepath);

            Log::info('Database backup created successfully', [
                'backup_path' => $filepath,
                'size' => $finalSize,
                'compressed' => $compress,
            ]);

            return [
                'success' => true,
                'message' => 'Database backup generated successfully.',
                'filepath' => $filepath,
            ];
        } catch (Exception $e) {
            Log::error('Database backup exception: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while generating the backup: ' . $e->getMessage(),
                'filepath' => null,
            ];
        }
    }

    /**
     * Restore a database backup file.
     *
     * @param string $filename
     * @return array{success:bool,message:string,filepath:?string}
     */
    public function restoreBackup(string $filename): array
    {
        try {
            $filepath = storage_path('backups/' . $filename);

            if (!File::exists($filepath)) {
                return [
                    'success' => false,
                    'message' => 'Backup file does not exist.',
                    'filepath' => null,
                ];
            }

            $realFile = realpath($filepath);
            $realBackupDir = realpath(storage_path('backups'));

            if (!$realFile || !$realBackupDir || strpos($realFile, $realBackupDir) !== 0) {
                Log::warning('Attempted restore outside backups directory', [
                    'backup_ref' => $filename,
                ]);

                return [
                    'success' => false,
                    'message' => 'Invalid backup file path.',
                    'filepath' => null,
                ];
            }

            $dbConfig = $this->getDatabaseConfig();
            $command = $this->buildMysqlRestoreCommand($dbConfig, $filepath);

            $output = [];
            $exitCode = 0;

            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                Log::error('Database restore failed', [
                    'backup_ref' => $filename,
                    'exit_code' => $exitCode,
                    'error' => implode(PHP_EOL, $output),
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to restore database backup. Check logs for details.',
                    'filepath' => $filepath,
                ];
            }

            Log::info('Database restored successfully', [
                'backup_ref' => $filename,
                'backup_path' => $filepath,
            ]);

            return [
                'success' => true,
                'message' => 'Database restored successfully.',
                'filepath' => $filepath,
            ];
        } catch (Exception $e) {
            Log::error('Database restore exception: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while restoring the backup: ' . $e->getMessage(),
                'filepath' => null,
            ];
        }
    }

    /**
     * Get database configuration.
     *
     * @return array{host:string,port:string|int,database:string,username:string,password:?string}
     */
    private function getDatabaseConfig(): array
    {
        return [
            'host' => (string) Config::get('database.connections.mysql.host'),
            'port' => (string) Config::get('database.connections.mysql.port', 3306),
            'database' => (string) Config::get('database.connections.mysql.database'),
            'username' => (string) Config::get('database.connections.mysql.username'),
            'password' => Config::get('database.connections.mysql.password'),
        ];
    }

    /**
     * Build the mysqldump command.
     *
     * @param array{host:string,port:string|int,database:string,username:string,password:?string} $dbConfig
     * @param string $filepath
     * @return string
     */
    private function buildMysqldumpCommand(array $dbConfig, string $filepath): string
    {
        $host = escapeshellarg($dbConfig['host']);
        $port = escapeshellarg((string) $dbConfig['port']);
        $database = escapeshellarg($dbConfig['database']);
        $username = escapeshellarg($dbConfig['username']);
        $filepathArg = escapeshellarg($filepath);

        $passwordPrefix = '';

        if (!empty($dbConfig['password'])) {
            $passwordPrefix = 'MYSQL_PWD=' . escapeshellarg((string) $dbConfig['password']) . ' ';
        }

        $command =
            $passwordPrefix .
            "mysqldump " .
            "--no-tablespaces " .
            "--single-transaction " .
            "--quick " .
            "--lock-tables=false " .
            "--host={$host} " .
            "--port={$port} " .
            "--user={$username} " .
            "{$database} > {$filepathArg} 2>&1";

        return 'bash -c ' . escapeshellarg($command);
    }

    /**
     * Build the mysql restore command.
     *
     * @param array{host:string,port:string|int,database:string,username:string,password:?string} $dbConfig
     * @param string $filepath
     * @return string
     */
    private function buildMysqlRestoreCommand(array $dbConfig, string $filepath): string
    {
        $host = escapeshellarg($dbConfig['host']);
        $port = escapeshellarg((string) $dbConfig['port']);
        $database = escapeshellarg($dbConfig['database']);
        $username = escapeshellarg($dbConfig['username']);
        $filepathArg = escapeshellarg($filepath);

        $passwordPrefix = '';

        if (!empty($dbConfig['password'])) {
            $passwordPrefix = 'MYSQL_PWD=' . escapeshellarg((string) $dbConfig['password']) . ' ';
        }

        $mysqlCommand =
            $passwordPrefix .
            "mysql " .
            "--host={$host} " .
            "--port={$port} " .
            "--user={$username} " .
            "{$database}";

        if (str_ends_with($filepath, '.gz')) {
            $fullCommand = "gunzip -c {$filepathArg} | {$mysqlCommand} 2>&1";
            return 'bash -o pipefail -c ' . escapeshellarg($fullCommand);
        }

        $fullCommand = "{$mysqlCommand} < {$filepathArg} 2>&1";
        return 'bash -c ' . escapeshellarg($fullCommand);
    }

    /**
     * Generate timestamped backup filename.
     *
     * @param bool $compress
     * @return string
     */
    private function generateBackupFilename(bool $compress = false): string
    {
        $timestamp = now()->format('Y_m_d_H_i_s');
        $extension = $compress ? '.sql.gz' : '.sql';
        $dbName = (string) Config::get('database.connections.mysql.database');

        return "{$dbName}_backup_{$timestamp}{$extension}";
    }

    /**
     * Ensure backup directory exists.
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
     * Get list of existing backups.
     *
     * @return array<int, array{filename:string,size:int,modified:int,path:string}>
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
     * Delete a backup file.
     *
     * @param string $filename
     * @return bool
     */
    public function deleteBackup(string $filename): bool
    {
        $filepath = storage_path('backups/' . $filename);
        $realFile = realpath($filepath);
        $realBackupDir = realpath(storage_path('backups'));

        if (!$realBackupDir) {
            return false;
        }

        if ($realFile === false) {
            return false;
        }

        if (strpos($realFile, $realBackupDir) !== 0) {
            Log::warning('Attempted to delete backup outside backups directory', [
                'backup_ref' => $filename,
            ]);

            return false;
        }

        if (File::exists($filepath)) {
            return File::delete($filepath);
        }

        return false;
    }

    /**
     * Clean up old backups based on retention days.
     *
     * @param int $retentionDays
     * @return int
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
            $fileAge = ($now - $file->getMTime()) / 86400;

            if ($fileAge > $retentionDays) {
                if (File::delete($file->getRealPath())) {
                    $deleted++;

                    Log::info('Deleted old backup', [
                        'backup_ref' => $file->getFilename(),
                        'age_days' => round($fileAge),
                    ]);
                }
            }
        }

        return $deleted;
    }
}
