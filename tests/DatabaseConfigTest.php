<?php

declare(strict_types=1);

namespace BackupManager\Laravel\Tests;

use BackupManager\Databases\DatabaseProvider;
use BackupManager\Databases\DatabaseTypeNotSupported;
use BackupManager\Databases\MysqlDatabase;

class DatabaseConfigTest extends TestCase
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function mysqlConnection(array $overrides = []): array
    {
        return array_merge([
            'driver' => 'mysql',
            'host' => 'db.example',
            'port' => '3307',
            'database' => 'app',
            'username' => 'user',
            'password' => 'secret',
        ], $overrides);
    }

    private function dumpCommandFor(string $connection): string
    {
        return $this->app->make(DatabaseProvider::class)->get($connection)->getDumpCommandLine('out.sql');
    }

    public function test_it_maps_a_mysql_connection(): void
    {
        config(['database.connections.app' => $this->mysqlConnection()]);

        $this->assertSame(
            "mysqldump --routines  --host='db.example' --port='3307' --user='user' --password='secret' 'app' > 'out.sql'",
            $this->dumpCommandFor('app')
        );
    }

    public function test_a_unix_socket_replaces_host_and_port(): void
    {
        config(['database.connections.app' => $this->mysqlConnection([
            'host' => null,
            'unix_socket' => '/run/mysqld/mysqld.sock',
        ])]);

        $command = $this->dumpCommandFor('app');

        $this->assertStringContainsString("--socket='/run/mysqld/mysqld.sock'", $command);
        $this->assertStringNotContainsString('--host', $command);
        $this->assertStringNotContainsString('--port', $command);
    }

    public function test_it_passes_mysql_backup_options_from_the_connection(): void
    {
        config(['database.connections.app' => $this->mysqlConnection([
            'singleTransaction' => true,
            'ignoreTables' => ['sessions'],
            'onlyTables' => ['users', 'sessions'],
            'columnStatistics' => false,
            'extraParams' => '--skip-comments',
        ])]);

        $command = $this->dumpCommandFor('app');

        $this->assertStringContainsString('--single-transaction', $command);
        $this->assertStringContainsString("--ignore-table='app.sessions'", $command);
        $this->assertStringContainsString("'app' 'users' 'sessions'", $command);
        $this->assertStringContainsString('--column-statistics=0', $command);
        $this->assertStringContainsString('--skip-comments', $command);
    }

    public function test_command_extra_params_apply_when_the_connection_sets_none(): void
    {
        config(['backup-manager.command-extra-params' => '--max-allowed-packet=64M']);
        config(['database.connections.app' => $this->mysqlConnection()]);

        $this->assertStringContainsString('--max-allowed-packet=64M', $this->dumpCommandFor('app'));
    }

    public function test_a_mariadb_connection_is_backed_up_as_mysql(): void
    {
        config(['database.connections.app' => $this->mysqlConnection(['driver' => 'mariadb'])]);

        $this->assertInstanceOf(MysqlDatabase::class, $this->app->make(DatabaseProvider::class)->get('app'));
    }

    public function test_an_unsupported_driver_is_reported_as_unsupported(): void
    {
        config(['database.connections.lite' => ['driver' => 'sqlite', 'database' => ':memory:']]);

        $this->expectException(DatabaseTypeNotSupported::class);

        $this->app->make(DatabaseProvider::class)->get('lite');
    }

    public function test_a_resolved_database_provider_sees_connections_set_at_runtime(): void
    {
        $databases = $this->app->make(DatabaseProvider::class);

        config(['database.connections.tenant' => $this->mysqlConnection(['database' => 'tenant_42'])]);

        $this->assertStringContainsString("'tenant_42'", $databases->get('tenant')->getDumpCommandLine('out.sql'));
    }

    public function test_a_read_write_connection_is_backed_up_from_the_write_host(): void
    {
        config(['database.connections.app' => $this->mysqlConnection([
            'host' => null,
            'read' => ['host' => ['replica.example']],
            'write' => ['host' => ['primary.example']],
        ])]);

        $this->assertStringContainsString("--host='primary.example'", $this->dumpCommandFor('app'));
    }

    public function test_a_connection_url_is_used(): void
    {
        config(['database.connections.app' => [
            'driver' => 'mysql',
            'url' => 'mysql://user:p%40ss@db.example:3307/shop?unix_socket=/run/mysqld/mysqld.sock',
            'host' => '127.0.0.1',
            'database' => 'forge',
        ]]);

        $command = $this->dumpCommandFor('app');

        $this->assertStringContainsString("--socket='/run/mysqld/mysqld.sock'", $command);
        $this->assertStringContainsString("--user='user'", $command);
        $this->assertStringContainsString("--password='p@ss'", $command);
        $this->assertStringContainsString("'shop'", $command);
    }

    public function test_a_postgres_url_selects_the_postgresql_database(): void
    {
        config(['database.connections.app' => ['driver' => 'pgsql', 'url' => 'postgres://user:secret@pg.example:5433/shop']]);

        $command = $this->dumpCommandFor('app');

        $this->assertStringContainsString("--host='pg.example'", $command);
        $this->assertStringContainsString("--port='5433'", $command);
        $this->assertStringContainsString("'shop'", $command);
    }

    public function test_an_empty_url_is_ignored(): void
    {
        config(['database.connections.app' => $this->mysqlConnection(['url' => ''])]);

        $this->assertStringContainsString("--host='db.example'", $this->dumpCommandFor('app'));
    }

    public function test_the_url_host_and_port_replace_the_connection_s(): void
    {
        config(['database.connections.app' => [
            'driver' => 'mysql',
            'url' => 'mysql://user:secret@db.example:3307/shop',
            'host' => '127.0.0.1',
            'port' => '3306',
        ]]);

        $command = $this->dumpCommandFor('app');

        $this->assertStringContainsString("--host='db.example' --port='3307'", $command);
        $this->assertStringNotContainsString('127.0.0.1', $command);
    }

    public function test_a_mariadb_url_is_backed_up_as_mysql(): void
    {
        config(['database.connections.app' => ['driver' => 'mariadb', 'url' => 'mariadb://user:secret@db.example:3306/shop']]);

        $this->assertInstanceOf(MysqlDatabase::class, $this->app->make(DatabaseProvider::class)->get('app'));
        $this->assertStringContainsString("'shop'", $this->dumpCommandFor('app'));
    }

    public function test_a_null_url_is_ignored(): void
    {
        config(['database.connections.app' => $this->mysqlConnection(['url' => null])]);

        $this->assertStringContainsString("--host='db.example'", $this->dumpCommandFor('app'));
    }
}
