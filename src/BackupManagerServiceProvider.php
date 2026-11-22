<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

use BackupManager\Compressors\CompressorProvider;
use BackupManager\Compressors\GzipCompressor;
use BackupManager\Compressors\NullCompressor;
use BackupManager\Databases\DatabaseProvider;
use BackupManager\Databases\MysqlDatabase;
use BackupManager\Databases\PostgresqlDatabase;
use BackupManager\Filesystems\AsyncAwsS3Filesystem;
use BackupManager\Filesystems\Awss3Filesystem;
use BackupManager\Filesystems\DropboxFilesystem;
use BackupManager\Filesystems\FilesystemProvider;
use BackupManager\Filesystems\FtpFilesystem;
use BackupManager\Filesystems\GcsFilesystem;
use BackupManager\Filesystems\LocalFilesystem;
use BackupManager\Filesystems\SftpFilesystem;
use BackupManager\Filesystems\WebdavFilesystem;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the backup-manager providers and the db:backup, db:restore and
 * db:list commands. Storage connections come from config/backup-manager.php,
 * database connections from config/database.php.
 *
 * To add a filesystem type, extend the binding in your own provider:
 * $this->app->extend(FilesystemProvider::class, fn ($p) => tap($p)->add(new MyFilesystem()));
 */
class BackupManagerServiceProvider extends ServiceProvider
{
    private const CONFIG_PATH = __DIR__ . '/../config/backup-manager.php';

    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'backup-manager');

        $this->app->bind(FilesystemProvider::class, static function (Application $app): FilesystemProvider {
            $config = $app->make(Repository::class);
            $provider = new FilesystemProvider(RuntimeConfig::fromCallback(
                static fn (): array => self::arrayValue($config->get('backup-manager'))
            ));
            $provider->add(new Awss3Filesystem());
            $provider->add(new AsyncAwsS3Filesystem());
            $provider->add(new GcsFilesystem());
            $provider->add(new DropboxFilesystem());
            $provider->add(new FtpFilesystem());
            $provider->add(new LocalFilesystem());
            $provider->add(new SftpFilesystem());
            $provider->add(new WebdavFilesystem());

            return $provider;
        });

        $this->app->bind(DatabaseProvider::class, static function (Application $app): DatabaseProvider {
            $config = $app->make(Repository::class);
            $provider = new DatabaseProvider(RuntimeConfig::fromCallback(
                static fn (): array => DatabaseConnections::map(
                    self::arrayValue($config->get('database.connections')),
                    $config->get('backup-manager.command-extra-params')
                )
            ));
            $provider->add(new MysqlDatabase());
            $provider->add(new PostgresqlDatabase());

            return $provider;
        });

        $this->app->bind(CompressorProvider::class, static function (): CompressorProvider {
            $provider = new CompressorProvider();
            $provider->add(new GzipCompressor());
            $provider->add(new NullCompressor());

            return $provider;
        });

        $this->commands([
            DbBackupCommand::class,
            DbRestoreCommand::class,
            DbListCommand::class,
        ]);
    }

    /**
     * @return array<mixed>
     */
    private static function arrayValue(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    public function boot(): void
    {
        $this->publishes([self::CONFIG_PATH => $this->app->configPath('backup-manager.php')], 'config');
    }
}
