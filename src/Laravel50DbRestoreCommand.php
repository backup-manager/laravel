<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class Laravel5DbRestoreCommand
 * @package BackupManager\Laravel
 */
class Laravel50DbRestoreCommand extends DbRestoreCommand {
    use Laravel50Compatibility;
}
