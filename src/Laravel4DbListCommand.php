<?php namespace BackupManager\Laravel;

use Illuminate\Console\Command;

/**
 * Class Laravel4DbListCommand
 * @package BackupManager\Laravel
 */
class Laravel4DbListCommand extends DbListCommand {
    use Laravel4Compatibility;
}
