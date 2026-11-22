<?php

declare(strict_types=1);

namespace BackupManager\Laravel\Tests;

use BackupManager\Procedures\RestoreProcedure;
use Mockery;

class DbRestoreCommandTest extends TestCase
{
    public function test_it_prompts_for_the_backup_file_and_restores_it(): void
    {
        mkdir($this->storageRoot . '/backups');
        file_put_contents($this->storageRoot . '/backups/first.sql.gz', 'x');
        file_put_contents($this->storageRoot . '/backups/second.sql.gz', 'x');

        $restore = Mockery::mock(RestoreProcedure::class);
        $restore->shouldReceive('run')->once()->with('local', 'backups/second.sql.gz', 'mysql', 'gzip');
        $this->app->instance(RestoreProcedure::class, $restore);

        $this->artisan('db:restore', ['--source' => 'local', '--database' => 'mysql', '--compression' => 'gzip'])
            ->expectsQuestion('From which directory do you want to restore?', 'backups')
            ->expectsChoice('Which database dump do you want to restore?', 'second.sql.gz', ['first.sql.gz', 'second.sql.gz'])
            ->expectsConfirmation('Are these correct?', 'yes')
            ->expectsOutputToContain('Successfully restored')
            ->assertSuccessful();
    }

    public function test_it_reports_an_empty_directory(): void
    {
        $this->artisan('db:restore', ['--source' => 'local', '--database' => 'mysql', '--compression' => 'gzip'])
            ->expectsQuestion('From which directory do you want to restore?', '')
            ->expectsOutputToContain('No backups were found in this directory.')
            ->assertFailed();
    }

    public function test_it_fails_instead_of_prompting_when_not_interactive(): void
    {
        $this->artisan('db:restore', ['--source' => 'local', '--no-interaction' => true])
            ->expectsOutputToContain('Missing options: --sourcePath, --database, --compression')
            ->assertFailed();
    }
}
