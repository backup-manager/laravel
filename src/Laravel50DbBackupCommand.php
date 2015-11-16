<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class Laravel5DbBackupCommand
 * @package BackupManager\Laravel
 */
class Laravel50DbBackupCommand extends DbBackupCommand {
    use Laravel50Compatibility;
}
