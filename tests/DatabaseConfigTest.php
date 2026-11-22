<?php

namespace BackupManager\Laravel\Tests;

use BackupManager\Databases\DatabaseProvider;
use BackupManager\Databases\DatabaseTypeNotSupported;
use BackupManager\Databases\MysqlDatabase;

class DatabaseConfigTest extends TestCase
{
    private function mysqlConnection(array $overrides = [])
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

    private function dumpCommandFor($connection)
    {
        return $this->app->make(DatabaseProvider::class)->get($connection)->getDumpCommandLine('out.sql');
    }

    public function test_it_maps_a_mysql_connection()
    {
        config(['database.connections.app' => $this->mysqlConnection()]);

        $this->assertSame(
            "mysqldump --routines  --host='db.example' --port='3307' --user='user' --password='secret' 'app' > 'out.sql'",
            $this->dumpCommandFor('app')
        );
    }

    public function test_a_unix_socket_replaces_host_and_port()
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

    public function test_it_passes_mysql_backup_options_from_the_connection()
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

    public function test_command_extra_params_apply_when_the_connection_sets_none()
    {
        config(['backup-manager.command-extra-params' => '--max-allowed-packet=64M']);
        config(['database.connections.app' => $this->mysqlConnection()]);

        $this->assertStringContainsString('--max-allowed-packet=64M', $this->dumpCommandFor('app'));
    }

    public function test_a_mariadb_connection_is_backed_up_as_mysql()
    {
        config(['database.connections.app' => $this->mysqlConnection(['driver' => 'mariadb'])]);

        $this->assertInstanceOf(MysqlDatabase::class, $this->app->make(DatabaseProvider::class)->get('app'));
    }

    public function test_an_unsupported_driver_is_reported_as_unsupported()
    {
        config(['database.connections.lite' => ['driver' => 'sqlite', 'database' => ':memory:']]);

        $this->expectException(DatabaseTypeNotSupported::class);

        $this->app->make(DatabaseProvider::class)->get('lite');
    }

    public function test_a_resolved_database_provider_sees_connections_set_at_runtime()
    {
        $databases = $this->app->make(DatabaseProvider::class);

        config(['database.connections.tenant' => $this->mysqlConnection(['database' => 'tenant_42'])]);

        $this->assertStringContainsString("'tenant_42'", $databases->get('tenant')->getDumpCommandLine('out.sql'));
    }
}
