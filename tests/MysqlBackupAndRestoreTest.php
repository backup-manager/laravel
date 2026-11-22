<?php

declare(strict_types=1);

namespace BackupManager\Laravel\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Backs up and restores a real MySQL database with mysqldump and mysql.
 * Runs when BACKUP_MANAGER_TEST_MYSQL_PASSWORD is set (CI starts the runner's
 * MySQL server and sets it).
 */
class MysqlBackupAndRestoreTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('BACKUP_MANAGER_TEST_MYSQL_PASSWORD') === false) {
            $this->markTestSkipped('Set BACKUP_MANAGER_TEST_MYSQL_PASSWORD to run against MySQL.');
        }
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $base = [
            'driver' => 'mysql',
            'database' => 'backup_manager_test',
            'username' => 'root',
            'password' => getenv('BACKUP_MANAGER_TEST_MYSQL_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ];
        $app['config']->set('database.connections.tcp', $base + ['host' => '127.0.0.1', 'port' => '3306']);
        $app['config']->set('database.connections.socket', $base + [
            // Unreachable (TEST-NET-1): passes only if the socket replaces host and port.
            'host' => '192.0.2.1',
            'port' => '3306',
            'unix_socket' => getenv('BACKUP_MANAGER_TEST_MYSQL_SOCKET') ?: '/var/run/mysqld/mysqld.sock',
        ]);
    }

    public function test_it_backs_up_and_restores_over_tcp(): void
    {
        $this->backUpAndRestore('tcp');
    }

    public function test_it_backs_up_and_restores_over_a_unix_socket(): void
    {
        $this->backUpAndRestore('socket');
    }

    private function backUpAndRestore(string $connection): void
    {
        $db = DB::connection($connection);
        $db->statement('DROP TABLE IF EXISTS widgets');
        $db->statement('CREATE TABLE widgets (id INT PRIMARY KEY, name VARCHAR(32))');
        $db->table('widgets')->insert(['id' => 1, 'name' => $connection]);

        $this->assertSame(0, Artisan::call('db:backup', [
            '--database' => $connection,
            '--destination' => 'local',
            '--destinationPath' => "backups/{$connection}",
            '--compression' => 'gzip',
        ]));
        $this->assertFileExists("{$this->storageRoot}/backups/{$connection}.gz");

        $db->statement('DROP TABLE widgets');

        $this->assertSame(0, Artisan::call('db:restore', [
            '--source' => 'local',
            '--sourcePath' => "backups/{$connection}.gz",
            '--database' => $connection,
            '--compression' => 'gzip',
        ]));
        $this->assertSame($connection, $db->table('widgets')->where('id', 1)->value('name'));
    }
}
