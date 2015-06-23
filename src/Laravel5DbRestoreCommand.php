<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class Laravel5DbRestoreCommand
 * @package BackupManager\Laravel
 */
class Laravel5DbRestoreCommand extends DbRestoreCommand {
    use Laravel5Compatibility;
}
