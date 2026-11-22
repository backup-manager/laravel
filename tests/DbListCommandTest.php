<?php

declare(strict_types=1);

namespace BackupManager\Laravel\Tests;

use Illuminate\Support\Facades\Artisan;

class DbListCommandTest extends TestCase
{
    public function test_it_lists_the_backup_files_in_a_directory(): void
    {
        mkdir($this->storageRoot . '/backups/nested', 0777, true);
        file_put_contents($this->storageRoot . '/backups/2026-11-22.sql.gz', str_repeat('x', 2048));

        Artisan::call('db:list', ['--source' => 'local', '--path' => 'backups']);
        $output = Artisan::output();

        $this->assertStringContainsString('2026-11-22.sql.gz', $output);
        $this->assertStringContainsString('2 KB', $output);
        $this->assertStringNotContainsString('nested', $output);
    }

    public function test_it_lists_the_storage_root_when_no_path_is_given(): void
    {
        file_put_contents($this->storageRoot . '/root.sql', 'x');

        Artisan::call('db:list', ['--source' => 'local']);

        $this->assertStringContainsString('root.sql', Artisan::output());
    }

    public function test_it_fails_instead_of_prompting_when_not_interactive(): void
    {
        $this->artisan('db:list', ['--no-interaction' => true])
            ->expectsOutputToContain('Missing options: --source')
            ->assertFailed();
    }
}
