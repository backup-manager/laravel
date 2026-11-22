<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

class_alias(DbBackupCommand::class, Laravel55DbBackupCommand::class);

if (false) {
    /**
     * @deprecated since 3.0, use {@see DbBackupCommand}; removed in 4.0.
     */
    class Laravel55DbBackupCommand extends DbBackupCommand
    {
    }
}
