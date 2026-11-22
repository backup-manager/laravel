<?php

namespace BackupManager\Laravel\Tests;

use BackupManager\Filesystems\FilesystemProvider;
use BackupManager\Filesystems\FilesystemTypeNotSupported;
use BackupManager\Laravel\Lumen55ServiceProvider;
use BackupManager\ShellProcessing\ShellProcessor;

class LumenServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [Lumen55ServiceProvider::class];
    }

    public function test_it_builds_the_shell_processor()
    {
        $this->assertInstanceOf(ShellProcessor::class, $this->app->make(ShellProcessor::class));
    }

    public function test_it_registers_the_gcs_filesystem_type()
    {
        config(['backup-manager.gcs' => ['type' => 'Gcs', 'project' => 'p', 'keyFilePath' => null, 'bucket' => 'b', 'root' => '']]);

        try {
            $this->app->make(FilesystemProvider::class)->get('gcs');
        } catch (FilesystemTypeNotSupported $notRegistered) {
            $this->fail('Gcs is not registered: ' . $notRegistered->getMessage());
        } catch (\Throwable $adapterMissingOrConfig) {
            // Registered; building it needs the Gcs adapter package and credentials.
        }

        $this->addToAssertionCount(1);
    }
}
