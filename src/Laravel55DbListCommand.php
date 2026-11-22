<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

class_alias(DbListCommand::class, Laravel55DbListCommand::class);

if (false) {
    /**
     * @deprecated since 3.0, use {@see DbListCommand}; removed in 4.0.
     */
    class Laravel55DbListCommand extends DbListCommand
    {
    }
}
