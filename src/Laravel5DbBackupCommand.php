<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class BaseCommand
 * @package BackupManager\Laravel
 */
class Laravel5DbBackupCommand extends DbBackupCommand {
    use Laravel5Compatibility;
}
