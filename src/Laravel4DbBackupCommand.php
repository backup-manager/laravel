<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class Laravel4DbBackupCommand
 * @package BackupManager\Laravel
 */
class Laravel4DbBackupCommand extends DBBackupCommand {
    use Laravel4Compatibility;
}
