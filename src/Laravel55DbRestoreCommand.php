<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

class_alias(DbRestoreCommand::class, Laravel55DbRestoreCommand::class);

if (false) {
    /**
     * @deprecated since 3.0, use {@see DbRestoreCommand}; removed in 4.0.
     */
    class Laravel55DbRestoreCommand extends DbRestoreCommand
    {
    }
}
