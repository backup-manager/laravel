<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

use BackupManager\Databases\DatabaseProvider;
use BackupManager\Filesystems\Destination;
use BackupManager\Filesystems\FilesystemProvider;
use BackupManager\Procedures\BackupProcedure;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db:backup')]
final class DbBackupCommand extends Command
{
    use PromptsForOptions;

    /** @var string */
    protected $signature = 'db:backup
        {--database= : Database connection name (config/database.php)}
        {--destination= : Storage connection name (config/backup-manager.php)}
        {--destinationPath= : File path on the storage connection}
        {--compression= : Compression type: gzip or null}
        {--timestamp= : Date format appended to the file path, e.g. Y-m-d_H-i-s}';

    /** @var string */
    protected $description = 'Create database dump and save it on a service';

    public function handle(BackupProcedure $backup, DatabaseProvider $databases, FilesystemProvider $filesystems): int
    {
        $missing = $this->missingOptions(['database', 'destination', 'destinationPath', 'compression']);
        if ($this->refuseToPrompt($missing)) {
            return self::FAILURE;
        }

        if ($missing !== []) {
            $this->info('These options have not been given yet: <comment>' . implode(', ', $missing) . '</comment>');
            do {
                foreach ($missing as $option) {
                    match ($option) {
                        'database' => $this->askChoice('database', 'From which database connection do you want to dump?', $databases->getAvailableProviders()),
                        'destination' => $this->askChoice('destination', 'To which storage connection do you want to save?', $filesystems->getAvailableProviders()),
                        'destinationPath' => $this->askText('destinationPath', 'How do you want to name the backup?'),
                        default => $this->askChoice('compression', 'Which compression type do you want to use?', ['gzip', 'null']),
                    };
                }
            } while (!$this->confirmed(sprintf(
                'Do you want to create a backup of <comment>%s</comment>, store it on <comment>%s</comment> at <comment>%s</comment> and compress it with <comment>%s</comment>?',
                (string) $this->stringOption('database'),
                (string) $this->stringOption('destination'),
                $this->destinationPath(),
                (string) $this->stringOption('compression')
            )));
        }

        $destination = new Destination((string) $this->stringOption('destination'), $this->destinationPath());

        $this->info('Dumping database and uploading...');
        $backup->run((string) $this->stringOption('database'), [$destination], $this->stringOption('compression'));

        $this->newLine();
        $this->info(sprintf(
            'Successfully dumped <comment>%s</comment>, compressed with <comment>%s</comment> and stored it on <comment>%s</comment> at <comment>%s</comment>',
            (string) $this->stringOption('database'),
            (string) $this->stringOption('compression'),
            $destination->destinationFilesystem(),
            $destination->destinationPath()
        ));

        return self::SUCCESS;
    }

    private function destinationPath(): string
    {
        return $this->stringOption('destinationPath') . date((string) $this->stringOption('timestamp'));
    }
}
