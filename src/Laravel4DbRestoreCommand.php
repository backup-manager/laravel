<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class Laravel4DbRestoreCommand
 * @package BackupManager\Laravel
 */
class Laravel4DbRestoreCommand extends DbRestoreCommand {
    use Laravel4Compatibility;
}
