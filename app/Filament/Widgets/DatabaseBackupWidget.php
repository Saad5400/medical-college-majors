<?php

namespace App\Filament\Widgets;

use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class DatabaseBackupWidget extends Widget
{
    protected string $view = 'filament.widgets.database-backup-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function download(): ?BinaryFileResponse
    {
        if (! static::canView()) {
            abort(403);
        }

        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}");

        if (! is_array($connection) || ($connection['driver'] ?? null) !== 'pgsql') {
            Notification::make()
                ->title('Database backup unavailable')
                ->body('The default database connection is not PostgreSQL.')
                ->danger()
                ->send();

            return null;
        }

        $database = (string) ($connection['database'] ?? '');
        $username = (string) ($connection['username'] ?? '');

        if ($database === '') {
            Notification::make()
                ->title('Database backup failed')
                ->body('The database name is not configured.')
                ->danger()
                ->send();

            return null;
        }

        if ($username === '') {
            Notification::make()
                ->title('Database backup failed')
                ->body('The database username is not configured.')
                ->danger()
                ->send();

            return null;
        }

        $executable = (new ExecutableFinder)->find('pg_dump');

        if ($executable === null) {
            Notification::make()
                ->title('Database backup failed')
                ->body('pg_dump is not available in the app container.')
                ->danger()
                ->send();

            return null;
        }

        $backupDirectory = storage_path('app/private');

        if (! File::exists($backupDirectory)) {
            File::makeDirectory($backupDirectory, 0755, true);
        }

        $filename = sprintf('database-backup-%s.sql', now()->format('Ymd_His'));
        $filePath = $backupDirectory.DIRECTORY_SEPARATOR.$filename;

        $process = new Process([
            $executable,
            '--format=plain',
            '--no-owner',
            '--no-privileges',
            '--file',
            $filePath,
            '--host',
            (string) ($connection['host'] ?? '127.0.0.1'),
            '--port',
            (string) ($connection['port'] ?? 5432),
            '--username',
            $username,
            '--no-password',
            $database,
        ]);

        $password = (string) ($connection['password'] ?? '');

        if ($password !== '') {
            $process->setEnv(['PGPASSWORD' => $password]);
        }

        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            File::delete($filePath);

            Notification::make()
                ->title('Database backup failed')
                ->body(trim($process->getErrorOutput()) ?: 'pg_dump failed to create the backup file.')
                ->danger()
                ->send();

            return null;
        }

        return response()
            ->download($filePath, $filename)
            ->deleteFileAfterSend(true);
    }
}
