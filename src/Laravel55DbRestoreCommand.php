<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class Laravel55DbRestoreCommand
 * @package BackupManager\Laravel
 */
class Laravel55DbRestoreCommand extends DbRestoreCommand {
    use Laravel55Compatibility;
}
