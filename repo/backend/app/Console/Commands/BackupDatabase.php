<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--full : Force a full backup regardless of schedule}';

    protected $description = 'Back up the PostgreSQL database (incremental daily, full on Sundays)';

    public function handle(): int
    {
        $isFullBackup = $this->option('full') || now()->isSunday();
        $type = $isFullBackup ? 'full' : 'incremental';

        $startedAt = now();

        $backupDir = storage_path('app/backups');

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_{$type}_{$timestamp}.sql.gz";
        $filePath = $backupDir . '/' . $filename;

        $host = config('database.connections.pgsql.host', '127.0.0.1');
        $port = config('database.connections.pgsql.port', '5432');
        $database = config('database.connections.pgsql.database', 'forge');
        $username = config('database.connections.pgsql.username', 'forge');
        $password = config('database.connections.pgsql.password', '');

        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s %s %s | gzip > %s',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $isFullBackup ? '' : '--data-only',
            escapeshellarg($database),
            escapeshellarg($filePath),
        );

        try {
            $result = Process::timeout(600)->run($command);

            if (! $result->successful() || ! file_exists($filePath)) {
                $errorMessage = $result->errorOutput() ?: 'Backup file was not created.';

                BackupLog::create([
                    'type' => $type,
                    'file_path' => $filePath,
                    'file_size_bytes' => 0,
                    'checksum' => null,
                    'is_encrypted' => false,
                    'started_at' => $startedAt,
                    'completed_at' => now(),
                    'is_successful' => false,
                    'error_message' => $errorMessage,
                ]);

                $this->error("Backup failed: {$errorMessage}");

                return self::FAILURE;
            }

            $checksum = hash_file('sha256', $filePath);
            $fileSize = filesize($filePath);

            BackupLog::create([
                'type' => $type,
                'file_path' => $filePath,
                'file_size_bytes' => $fileSize,
                'checksum' => $checksum,
                'is_encrypted' => false,
                'started_at' => $startedAt,
                'completed_at' => now(),
                'is_successful' => true,
                'error_message' => null,
            ]);

            $this->info("Backup completed: {$filename} ({$type}, {$fileSize} bytes, SHA-256: {$checksum})");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            BackupLog::create([
                'type' => $type,
                'file_path' => $filePath,
                'file_size_bytes' => 0,
                'checksum' => null,
                'is_encrypted' => false,
                'started_at' => $startedAt,
                'completed_at' => now(),
                'is_successful' => false,
                'error_message' => $e->getMessage(),
            ]);

            $this->error("Backup failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
