<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

use BackupManager\Databases\DatabaseProvider;
use BackupManager\Filesystems\FilesystemProvider;
use BackupManager\Procedures\RestoreProcedure;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db:restore')]
final class DbRestoreCommand extends Command
{
    use ListsBackupFiles;
    use PromptsForOptions;

    /** @var string */
    protected $signature = 'db:restore
        {--source= : Storage connection name (config/backup-manager.php)}
        {--sourcePath= : Backup file path on the storage connection}
        {--database= : Database connection name (config/database.php)}
        {--compression= : Compression type: gzip or null}';

    /** @var string */
    protected $description = 'Restore a database backup.';

    public function handle(RestoreProcedure $restore, FilesystemProvider $filesystems, DatabaseProvider $databases): int
    {
        $missing = $this->missingOptions(['source', 'sourcePath', 'database', 'compression']);
        if ($this->refuseToPrompt($missing)) {
            return self::FAILURE;
        }

        if ($missing !== []) {
            $this->info('These options have not been given yet: <comment>' . implode(', ', $missing) . '</comment>');
            do {
                foreach ($missing as $option) {
                    $asked = match ($option) {
                        'source' => $this->askChoice('source', 'From which storage connection do you want to restore?', $filesystems->getAvailableProviders()),
                        'sourcePath' => $this->askSourcePath($filesystems),
                        'database' => $this->askChoice('database', 'To which database connection do you want to restore?', $databases->getAvailableProviders()),
                        default => $this->askChoice('compression', 'With which compression type was the backup made?', ['gzip', 'null']),
                    };
                    if ($asked === false) {
                        return self::FAILURE;
                    }
                }
            } while (!$this->confirmed(sprintf(
                'Do you want to restore the backup <comment>%s</comment> from <comment>%s</comment> to database <comment>%s</comment>, decompressing it with <comment>%s</comment>?',
                $this->stringOption('sourcePath'),
                $this->stringOption('source'),
                $this->stringOption('database'),
                $this->stringOption('compression')
            )));
        }

        $this->info('Downloading and importing backup...');
        $restore->run(
            (string) $this->stringOption('source'),
            (string) $this->stringOption('sourcePath'),
            (string) $this->stringOption('database'),
            $this->stringOption('compression')
        );

        $this->newLine();
        $this->info(sprintf(
            'Successfully restored <comment>%s</comment> from <comment>%s</comment> to database <comment>%s</comment>.',
            $this->stringOption('sourcePath'),
            $this->stringOption('source'),
            $this->stringOption('database')
        ));

        return self::SUCCESS;
    }

    /**
     * @return bool false when the directory holds no backups
     */
    private function askSourcePath(FilesystemProvider $filesystems): bool
    {
        $directory = (string) $this->ask('From which directory do you want to restore?', '');
        $files = $this->listBackupFiles($filesystems->get((string) $this->stringOption('source')), $directory);

        if ($files === []) {
            $this->error('No backups were found in this directory.');

            return false;
        }

        $this->info('Available database dumps:');
        $this->renderBackupFiles($files);
        $names = array_map(static fn ($file): string => basename($file->path()), $files);
        $filename = $this->choice('Which database dump do you want to restore?', $names);
        $this->input->setOption('sourcePath', self::joinPath($directory, is_array($filename) ? (string) reset($filename) : $filename));

        return true;
    }
}
