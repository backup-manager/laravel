# Laravel Driver for the Database Backup Manager

[![Tests](https://github.com/backup-manager/laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/backup-manager/laravel/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/backup-manager/laravel)](https://packagist.org/packages/backup-manager/laravel)
[![PHP Version](https://img.shields.io/packagist/dependency-v/backup-manager/laravel/php)](https://packagist.org/packages/backup-manager/laravel)
[![Laravel](https://img.shields.io/badge/Laravel-7%E2%80%9313-FF2D20?logo=laravel&logoColor=white)](#requirements)
[![Downloads](https://img.shields.io/packagist/dt/backup-manager/laravel)](https://packagist.org/packages/backup-manager/laravel/stats)
[![License](https://img.shields.io/packagist/l/backup-manager/laravel)](LICENSE)

Back up and restore your Laravel application's MySQL and PostgreSQL databases to S3, Google Cloud Storage, Dropbox, FTP, SFTP, WebDAV or local disk, from Artisan or from your own code.

This package integrates the framework-agnostic [Backup Manager](https://github.com/backup-manager/backup-manager) into **Laravel**. For the core library or the Symfony integration, see [the core repository](https://github.com/backup-manager/backup-manager).

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Artisan commands](#artisan-commands)
- [Using the Manager in code](#using-the-manager-in-code)
- [Scheduling backups](#scheduling-backups)
- [Lumen](#lumen)
- [Supported versions](#supported-versions)
- [Contributing](#contributing)
- [License](#license)

## Requirements

- PHP 7.4+
- Laravel 7 to 13 (CI tests every major). Laravel 12 and 13 are the versions
  Laravel still patches; Composer refuses older Laravel releases by default
  because each has an unpatched security advisory.
- MySQL or MariaDB: the `mysqldump` and `mysql` binaries
- PostgreSQL: the `pg_dump` and `psql` binaries
- Gzip compression: the `gzip` and `gunzip` binaries

## Installation

```shell
composer require backup-manager/laravel
```

The service provider is registered by package discovery. Publish the storage configuration:

```shell
php artisan vendor:publish --provider="BackupManager\Laravel\Laravel55ServiceProvider"
```

Then install the Flysystem adapter package for each storage type you use. Laravel 9 and later use Flysystem 3:

| Type | Package (Flysystem 3) |
|---|---|
| `Local` | included |
| `AwsS3` | `league/flysystem-aws-s3-v3` |
| `AsyncAwsS3` | `league/flysystem-async-aws-s3` |
| `Gcs` | `league/flysystem-google-cloud-storage` |
| `Dropbox` | `spatie/flysystem-dropbox` |
| `Ftp` | `league/flysystem-ftp` |
| `Sftp` | `league/flysystem-sftp-v3` |
| `Webdav` | `league/flysystem-webdav` |

Laravel 7 and 8 use Flysystem 1; see the [core adapter table](https://github.com/backup-manager/backup-manager/tree/3.x#installation) for the matching packages. If a package is missing, the backup fails with an error naming the package to install.

## Configuration

**Storage** connections live in `config/backup-manager.php`. Each entry has a `type` and that type's settings. S3-compatible services (DigitalOcean Spaces, MinIO, ...) use the `AwsS3` type with an `endpoint` (and `use_path_style_endpoint` where the service needs it).

**Databases** come from Laravel's own `config/database.php` connections; `mysql`, `mariadb` and `pgsql` connections can be backed up. MySQL and MariaDB connections accept these extra keys, which Laravel ignores and the backup uses:

```php
'mysql' => [
    'driver' => 'mysql',
    // ... Laravel's usual keys; url (DB_URL) and unix_socket are used when set ...
    'singleTransaction' => true,         // --single-transaction (InnoDB)
    'ignoreTables' => ['sessions'],      // --ignore-table for each
    'onlyTables' => ['users', 'orders'], // dump only these tables
    'ssl' => false,                      // --ssl
    'columnStatistics' => false,         // --column-statistics=0 (MySQL 8 mysqldump against older servers)
    'extraParams' => '--max-allowed-packet=64M', // appended to mysqldump
],
```

`extraParams` defaults to the `BACKUP_MANAGER_EXTRA_PARAMS` environment variable for every connection that does not set it.

Configuration is read when a backup runs, so connections changed at runtime with `config([...])` are used, for example a tenant database selected before calling `db:backup`.

## Artisan commands

`db:backup`, `db:restore` and `db:list` prompt for any option you leave out.

```shell
php artisan db:backup --database=mysql --destination=s3 --destinationPath=project/backup --timestamp="Y-m-d_H-i-s" --compression=gzip
php artisan db:list --source=s3 --path=project
php artisan db:restore --source=s3 --sourcePath=project/backup2026-11-22_04-00-00.gz --database=mysql --compression=gzip
```

`--timestamp` is a [date format](https://www.php.net/manual/en/datetime.format.php) appended to the destination path; the compressor adds its extension (`.gz`).

## Using the Manager in code

`BackupManager\Manager` is resolved by the container:

```php
use BackupManager\Filesystems\Destination;
use BackupManager\Manager;

public function backup(Manager $manager)
{
    $manager->makeBackup()->run('mysql', [new Destination('s3', 'project/backup.sql')], 'gzip');
}
```

This package backs up databases only. To delete old backups, use your storage's lifecycle rules (for example S3 lifecycle expiration) or Laravel's `Storage` facade.

## Scheduling backups

```php
// routes/console.php (Laravel 11+) or the schedule() method of app/Console/Kernel.php
Schedule::command('db:backup --database=mysql --destination=s3 --destinationPath=' . app()->environment() . '/backup --timestamp="Y_m_d_H_i_s" --compression=gzip')
    ->twiceDaily(13, 21);
```

## Lumen

Register the provider and configuration in `bootstrap/app.php`, then copy `vendor/backup-manager/laravel/config/backup-manager.php` to `config/backup-manager.php`:

```php
$app->configure('backup-manager');
$app->register(BackupManager\Laravel\Lumen55ServiceProvider::class);
```

## Supported versions

| Version | Laravel | PHP | Core library | Support |
|---|---|---|---|---|
| 3.x | 12, 13 | 8.2+ | 4.x | active |
| 2.x | 7–13 | 7.4+ | 3.x | security fixes until 2027-12-31 |

## Contributing

Run the tests with `vendor/bin/phpunit`. The MySQL backup-and-restore test runs when `BACKUP_MANAGER_TEST_MYSQL_PASSWORD` is set (root user, database `backup_manager_test` on 127.0.0.1 and the default socket); CI runs it on every Laravel version. Proposals for new features are welcome as [issues](https://github.com/backup-manager/laravel/issues/new).

Maintained by [Shawn McCool](https://shawnmc.cool) and [contributors](https://github.com/backup-manager/laravel/graphs/contributors).

## License

[MIT](LICENSE). See [CHANGELOG.md](CHANGELOG.md) for release notes.
