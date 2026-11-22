<?php

namespace BackupManager\Laravel\Tests;

use BackupManager\Compressors\CompressorProvider;
use BackupManager\Filesystems\FilesystemProvider;
use BackupManager\Manager;
use BackupManager\ShellProcessing\ShellProcessor;
use Illuminate\Support\Facades\Artisan;

class ServiceProviderTest extends TestCase
{
    public function test_it_registers_the_artisan_commands()
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('db:backup', $commands);
        $this->assertArrayHasKey('db:restore', $commands);
        $this->assertArrayHasKey('db:list', $commands);
    }

    public function test_it_resolves_the_manager_and_its_providers()
    {
        $this->assertInstanceOf(Manager::class, $this->app->make(Manager::class));
        $this->assertInstanceOf(CompressorProvider::class, $this->app->make(CompressorProvider::class));
        $this->assertInstanceOf(ShellProcessor::class, $this->app->make(ShellProcessor::class));
    }

    public function test_storage_connections_exclude_scalar_settings()
    {
        $connections = $this->app->make(FilesystemProvider::class)->getAvailableProviders();

        $this->assertContains('local', $connections);
        $this->assertContains('s3', $connections);
        $this->assertNotContains('command-extra-params', $connections);
    }

    public function test_a_resolved_filesystem_provider_sees_config_set_at_runtime()
    {
        $filesystems = $this->app->make(FilesystemProvider::class);

        config(['backup-manager.tenant' => ['type' => 'Local', 'root' => $this->storageRoot . '/tenant']]);

        $this->assertSame($this->storageRoot . '/tenant', $filesystems->getConfig('tenant', 'root'));
        $this->assertNotNull($filesystems->get('tenant'));
    }
}
