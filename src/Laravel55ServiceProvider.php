<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

class_alias(BackupManagerServiceProvider::class, Laravel55ServiceProvider::class);

if (false) {
    /**
     * @deprecated since 3.0, use {@see BackupManagerServiceProvider}; removed in 4.0.
     */
    class Laravel55ServiceProvider extends BackupManagerServiceProvider
    {
    }
}
