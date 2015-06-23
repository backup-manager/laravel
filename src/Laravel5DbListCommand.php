<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class Laravel5DbListCommand
 * @package BackupManager\Laravel
 */
class Laravel5DbListCommand extends DbListCommand {
    use Laravel5Compatibility;
}
