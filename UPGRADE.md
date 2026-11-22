# Upgrading

## From 2.x to 3.0

### Requirements

- PHP 8.2 or later.
- Laravel 12.71+ or 13.40+. Every earlier Laravel release has an unpatched
  security advisory (CVE-2026-128083 and others). Laravel 7–11 users stay on
  2.x, which receives security fixes on the `2.x` branch.
- `backup-manager/backup-manager` ^4.1, which requires Flysystem 3. Install
  the Flysystem 3 adapter package for each storage type you use (see the
  README).

### Removed

| Removed | Replacement |
|---|---|
| `Lumen55ServiceProvider` | none; Lumen is discontinued. Stay on 2.x or move to Laravel. |
| `DropboxV2` storage type | `Dropbox` type with `spatie/flysystem-dropbox` (`token`, `root`) |
| `Rackspace` storage type and the default `rackspace` connection | `AwsS3` type with an S3-compatible service |
| traits `AutoComplete`, `Laravel55Compatibility`, `GetDatabaseConfig` | none; they were internal helpers of the commands and providers |

### Renamed

| 2.x | 3.0 |
|---|---|
| `Laravel55ServiceProvider` | `BackupManagerServiceProvider` |
| `Laravel55DbBackupCommand` | `DbBackupCommand` |
| `Laravel55DbRestoreCommand` | `DbRestoreCommand` |
| `Laravel55DbListCommand` | `DbListCommand` |

The `Laravel55*` names still work as deprecated aliases and are removed in
4.0. Package discovery registers the new provider; if you list the provider
in `config/app.php` or `bootstrap/providers.php`, change it to
`BackupManager\Laravel\BackupManagerServiceProvider`, and the vendor:publish
command to `--provider="BackupManager\Laravel\BackupManagerServiceProvider"`.

### Commands

- Names and options are unchanged.
- The command classes are `final` and receive their services in `handle()`;
  the constructors that took `BackupProcedure`, `RestoreProcedure`,
  `DatabaseProvider` and `FilesystemProvider` are gone, as is `fire()`.
- In a non-interactive run (`--no-interaction`, the scheduler) a missing
  required option is an error with exit code 1 instead of a prompt.
- `db:list` without `--path` lists the storage root instead of asking for a
  path.
- Choices (connections, compression, backup file) are asked with Laravel's
  `choice()` prompt.

### Extending

To add a filesystem type, extend the binding in your own service provider:

```php
$this->app->extend(FilesystemProvider::class, fn ($provider) => tap($provider)->add(new MyFilesystem()));
```
