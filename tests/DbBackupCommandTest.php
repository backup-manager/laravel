<?php

declare(strict_types=1);

namespace BackupManager\Laravel\Tests;

use BackupManager\Filesystems\Destination;
use BackupManager\Procedures\BackupProcedure;
use Mockery;

class DbBackupCommandTest extends TestCase
{
    public function test_it_asks_for_missing_options_and_backs_up(): void
    {
        config(['database.connections.app' => ['driver' => 'mysql', 'database' => 'app']]);

        $backup = Mockery::mock(BackupProcedure::class);
        $backup->shouldReceive('run')->once()->with('app', Mockery::on(
            static fn (array $destinations): bool => count($destinations) === 1
                && $destinations[0] instanceof Destination
                && $destinations[0]->destinationFilesystem() === 'local'
                && $destinations[0]->destinationPath() === 'backups/app'
        ), 'gzip');
        $this->app->instance(BackupProcedure::class, $backup);

        $this->artisan('db:backup', ['--database' => 'app', '--destination' => 'local', '--compression' => 'gzip'])
            ->expectsQuestion('How do you want to name the backup?', '')
            ->expectsQuestion('How do you want to name the backup?', 'backups/app')
            ->expectsConfirmation('Are these correct?', 'yes')
            ->expectsOutputToContain('Successfully dumped')
            ->assertSuccessful();
    }

    public function test_it_fails_instead_of_prompting_when_not_interactive(): void
    {
        $this->artisan('db:backup', ['--database' => 'app', '--no-interaction' => true])
            ->expectsOutputToContain('Missing options: --destination, --destinationPath, --compression')
            ->assertFailed();
    }
}
