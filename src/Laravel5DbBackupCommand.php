<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class BaseCommand
 * @package BackupManager\Laravel
 */
class Laravel5DbBackupCommand extends DBBackupCommand {
    use Laravel5Compatibility;
}
