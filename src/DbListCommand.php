<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

use BackupManager\Filesystems\FilesystemProvider;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db:list')]
final class DbListCommand extends Command
{
    use ListsBackupFiles;
    use PromptsForOptions;

    /** @var string */
    protected $signature = 'db:list
        {--source= : Storage connection name (config/backup-manager.php)}
        {--path= : Directory on the storage connection}';

    /** @var string */
    protected $description = 'List contents of a backup storage destination.';

    public function handle(FilesystemProvider $filesystems): int
    {
        if ($this->stringOption('source') === null) {
            if ($this->refuseToPrompt(['source'])) {
                return self::FAILURE;
            }
            $this->askChoice('source', 'From which storage connection do you want to list?', $filesystems->getAvailableProviders());
        }

        $source = (string) $this->stringOption('source');
        $this->renderBackupFiles($this->listBackupFiles($filesystems->get($source), (string) $this->stringOption('path')));

        return self::SUCCESS;
    }
}
