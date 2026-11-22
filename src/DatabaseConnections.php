<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

/**
 * Maps Laravel database connections (config/database.php) to backup-manager
 * database configs.
 *
 * @internal
 */
final class DatabaseConnections
{
    /**
     * MySQL options that the core library reads and Laravel ignores; set them
     * on the connection in config/database.php.
     */
    private const MYSQL_BACKUP_OPTIONS = ['singleTransaction', 'ignoreTables', 'onlyTables', 'ssl', 'columnStatistics'];

    /**
     * @param array<mixed> $connections Laravel's database.connections
     * @param mixed $defaultExtraParams backup-manager.command-extra-params
     * @return array<string, array<string, mixed>> keyed by connection name
     */
    public static function map(array $connections, mixed $defaultExtraParams): array
    {
        $mapped = [];
        foreach ($connections as $name => $connection) {
            if (is_array($connection)) {
                $mapped[(string) $name] = self::mapConnection($connection, $defaultExtraParams);
            }
        }

        return $mapped;
    }

    /**
     * @param array<mixed> $connection
     * @return array<string, mixed>
     */
    private static function mapConnection(array $connection, mixed $defaultExtraParams): array
    {
        $driver = $connection['driver'] ?? null;
        // mysqldump and mysql handle MariaDB.
        $type = $driver === 'mariadb' ? 'mysql' : $driver;

        if ($type !== 'mysql' && $type !== 'pgsql') {
            // The core library reports "database type ... is not currently supported".
            return ['type' => $driver];
        }

        // A read/write connection without a top-level host is backed up from its write host.
        $host = $connection['host'] ?? $connection['write']['host'] ?? null;
        if (is_array($host)) {
            $host = reset($host);
        }

        $mapped = [
            'type' => $type,
            'host' => $host,
            'port' => $connection['port'] ?? ($type === 'mysql' ? '3306' : '5432'),
            'user' => $connection['username'] ?? null,
            'pass' => $connection['password'] ?? null,
            'database' => $connection['database'] ?? null,
            'extraParams' => array_key_exists('extraParams', $connection) ? $connection['extraParams'] : $defaultExtraParams,
        ];

        if ($type === 'mysql') {
            if (!empty($connection['unix_socket'])) {
                $mapped['socket'] = $connection['unix_socket'];
            }
            foreach (self::MYSQL_BACKUP_OPTIONS as $option) {
                if (array_key_exists($option, $connection)) {
                    $mapped[$option] = $connection[$option];
                }
            }
        }

        return $mapped;
    }
}
