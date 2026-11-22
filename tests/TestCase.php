<?php

namespace BackupManager\Laravel\Tests;

use BackupManager\Laravel\Laravel55ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /** @var string */
    protected $storageRoot;

    protected function getPackageProviders($app)
    {
        return [Laravel55ServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->storageRoot = sys_get_temp_dir() . '/backup-manager-laravel-' . getmypid();
        if ( ! is_dir($this->storageRoot)) {
            mkdir($this->storageRoot, 0777, true);
        }
        $app['config']->set('backup-manager.local', ['type' => 'Local', 'root' => $this->storageRoot]);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->storageRoot);
        parent::tearDown();
    }

    private function removeDirectory($directory)
    {
        if ($directory === null || ! is_dir($directory)) {
            return;
        }
        foreach (array_diff(scandir($directory), ['.', '..']) as $entry) {
            $path = "{$directory}/{$entry}";
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($directory);
    }
}
