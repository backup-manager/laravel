<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class Laravel5DbListCommand
 * @package BackupManager\Laravel
 */
class Laravel50DbListCommand extends DbListCommand {
    use Laravel50Compatibility;
}
