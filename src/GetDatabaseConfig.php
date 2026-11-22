<?php namespace BackupManager\Laravel;

use BackupManager\Config\Config;
use Illuminate\Support\ConfigurationUrlParser;

/**
 * Maps Laravel database connections to backup-manager database configs.
 *
 * @package BackupManager\Laravel
 */
trait GetDatabaseConfig
{
    /**
     * @param array $connections Laravel's database.connections
     * @return Config
     */
    private function getDatabaseConfig($connections) {
        return new Config($this->mapDatabaseConnections($connections));
    }

    /**
     * @param array $connections Laravel's database.connections
     * @return array backup-manager database configs, keyed by connection name
     */
    private function mapDatabaseConnections($connections) {
        return array_map(function ($connection) {
            return $this->mapDatabaseConnection((array) $connection);
        }, (array) $connections);
    }

    /**
     * MySQL options that core reads and Laravel ignores; set them on the
     * connection in config/database.php.
     *
     * @return string[]
     */
    private function mysqlBackupOptions() {
        return ['singleTransaction', 'ignoreTables', 'onlyTables', 'ssl', 'columnStatistics'];
    }

    /**
     * @param array $connection
     * @return array
     */
    private function mapDatabaseConnection(array $connection) {
        if (isset($connection['url']) && is_string($connection['url']) && $connection['url'] !== '') {
            // DB_URL: Laravel's own parser; URL parts override the connection's keys, as Laravel connects.
            $connection = (new ConfigurationUrlParser())->parseConfiguration($connection);
        }

        $driver = isset($connection['driver']) ? $connection['driver'] : null;
        // Laravel 11+ has a mariadb driver; mysqldump and mysql handle MariaDB.
        $type = $driver === 'mariadb' ? 'mysql' : $driver;

        if ( ! in_array($type, ['mysql', 'pgsql'], true)) {
            // Core reports "database type ... is not currently supported" for it.
            return ['type' => $driver];
        }

        $host = isset($connection['host']) ? $connection['host'] : null;
        if ($host === null && isset($connection['write']['host'])) {
            // Read/write split: back up from the write (primary) connection.
            $host = $connection['write']['host'];
        }
        if (is_array($host)) {
            $host = reset($host);
        }

        $mapped = [
            'type'     => $type,
            'host'     => $host,
            'port'     => isset($connection['port']) ? $connection['port'] : ($type === 'mysql' ? '3306' : '5432'),
            'user'     => isset($connection['username']) ? $connection['username'] : null,
            'pass'     => isset($connection['password']) ? $connection['password'] : null,
            'database' => isset($connection['database']) ? $connection['database'] : null,
            'extraParams' => array_key_exists('extraParams', $connection)
                ? $connection['extraParams']
                : config('backup-manager.command-extra-params'),
        ];

        if ($type === 'mysql') {
            if ( ! empty($connection['unix_socket'])) {
                $mapped['socket'] = $connection['unix_socket'];
            }
            foreach ($this->mysqlBackupOptions() as $option) {
                if (array_key_exists($option, $connection)) {
                    $mapped[$option] = $connection[$option];
                }
            }
        }

        return $mapped;
    }
}
