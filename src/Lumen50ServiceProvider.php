<?php namespace BackupManager\Laravel;

use BackupManager\Databases;
use BackupManager\Filesystems;
use BackupManager\Compressors;
use Symfony\Component\Process\Process;
use Illuminate\Support\ServiceProvider;
use BackupManager\Config\Config;
use BackupManager\ShellProcessing\ShellProcessor;

/**
 * Class BackupManagerServiceProvider
 * @package BackupManager\Laravel
 */
class Lumen50ServiceProvider extends ServiceProvider {
    use GetDatabaseConfig;

    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $configPath = __DIR__ . '/../config/backup-manager.php';
        $this->mergeConfigFrom($configPath, 'backup-manager');
        $this->registerFilesystemProvider();
        $this->registerDatabaseProvider();
        $this->registerCompressorProvider();
        $this->registerShellProcessor();
        $this->registerArtisanCommands();
    }

    /**
     * Register the filesystem provider.
     *
     * @return void
     */
    private function registerFilesystemProvider() {
        $this->app->bind(\BackupManager\Filesystems\FilesystemProvider::class, function ($app) {
            $provider = new Filesystems\FilesystemProvider(new Config($app['config']['backup-manager']));
            $provider->add(new Filesystems\Awss3Filesystem);
            $provider->add(new Filesystems\DropboxFilesystem);
            $provider->add(new Filesystems\DropboxV2Filesystem);
            $provider->add(new Filesystems\FtpFilesystem);
            $provider->add(new Filesystems\LocalFilesystem);
            $provider->add(new Filesystems\RackspaceFilesystem);
            $provider->add(new Filesystems\SftpFilesystem);
            return $provider;
        });
    }

    /**
     * Register the database provider.
     *
     * @return void
     */
    private function registerDatabaseProvider() {
        $this->app->bind(\BackupManager\Databases\DatabaseProvider::class, function ($app) {
            $provider = new Databases\DatabaseProvider($this->getDatabaseConfig($app['config']['database.connections']));
            $provider->add(new Databases\MysqlDatabase);
            $provider->add(new Databases\PostgresqlDatabase);
            return $provider;
        });
    }

    /**
     * Register the compressor provider.
     *
     * @return void
     */
    private function registerCompressorProvider() {
        $this->app->bind(\BackupManager\Compressors\CompressorProvider::class, function () {
            $provider = new Compressors\CompressorProvider;
            $provider->add(new Compressors\GzipCompressor);
            $provider->add(new Compressors\NullCompressor);
            return $provider;
        });
    }

    /**
     * Register the filesystem provider.
     *
     * @return void
     */
    private function registerShellProcessor() {
        $this->app->bind(\BackupManager\ShellProcessing\ShellProcessor::class, function () {
            return new ShellProcessor(new Process(''));
        });
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerArtisanCommands() {
        $this->commands([
            \BackupManager\Laravel\Laravel50DbBackupCommand::class,
            \BackupManager\Laravel\Laravel50DbRestoreCommand::class,
            \BackupManager\Laravel\Laravel50DbListCommand::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return [
            \BackupManager\Filesystems\FilesystemProvider::class,
            \BackupManager\Databases\DatabaseProvider::class,
            \BackupManager\ShellProcessing\ShellProcessor::class,
        ];
    }
}
