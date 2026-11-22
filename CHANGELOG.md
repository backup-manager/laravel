# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project
adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [3.0.0] - unreleased

Requires PHP 8.2, Laravel 12 or 13, and backup-manager 4.1. See
[UPGRADE.md](UPGRADE.md).

### Changed
- Requires `backup-manager/backup-manager` ^4.1 (Flysystem 3), PHP 8.2+ and
  `illuminate/*` ^12.71.0 or ^13.40.0, the first releases patched for
  CVE-2026-128083. Laravel 7–11 stay on 2.x.
- `BackupManagerServiceProvider` replaces `Laravel55ServiceProvider`, and
  `DbBackupCommand`, `DbRestoreCommand` and `DbListCommand` replace the
  `Laravel55*` command classes; the old names remain as deprecated aliases.
- Commands are final, receive their services in `handle()`, and fail with a
  "Missing options" error instead of prompting when there is no terminal
  (scheduler, queued `Artisan::call()`, `--no-interaction`).
- `db:list` without `--path` lists the storage root.
- Native types and `strict_types`; PHPStan level 8 and Psalm level 2 in CI.

### Removed
- `Lumen55ServiceProvider` (Lumen is discontinued).
- The `DropboxV2` and `Rackspace` storage types, removed from core 4.0, and
  the default `rackspace` connection.
- The traits `AutoComplete`, `Laravel55Compatibility` and `GetDatabaseConfig`.

## [2.1.0] - 2026-11-22

### Added
- Laravel 8, 9, 10, 11, 12 and 13 support, and PHP 8. (reimplements #130 by
  @nafiesl and #135 by @martindrapeau)
- MySQL connections through `unix_socket` (no `host` needed). (#128 by
  @brucek2; uses backup-manager 3.3)
- MySQL backup options set on the Laravel connection in
  `config/database.php`: `singleTransaction`, `ignoreTables`, `onlyTables`,
  `ssl`, `columnStatistics` and `extraParams`. (#116, #101, #99)
- `mariadb` connections (Laravel 11+) are backed up as MySQL.
- `AsyncAwsS3` and `Webdav` storage types are registered.
- The default config documents `endpoint` and `use_path_style_endpoint` for
  S3-compatible services such as DigitalOcean Spaces. (#102)
- Tests (Orchestra Testbench) and CI on every supported Laravel major,
  including a MySQL backup and restore over TCP and a unix socket.

### Changed
- Requires `backup-manager/backup-manager` ^3.3 (Flysystem 1, 2 or 3), PHP
  7.4+ and Laravel 7+. `symfony/process` requires a release patched for
  CVE-2024-51736 and CVE-2026-24739, which Laravel 5.5–6 cannot install;
  Laravel 5.5–6 and PHP 7.3 stay on 2.0.
- A MySQL connection that sets both `host` and `unix_socket` (for example
  `DB_SOCKET` in Laravel's default config) is backed up through the socket,
  as Laravel itself connects; 2.0 used host and port.
- A read/write connection without a top-level `host` is backed up from its
  `write` host (2.0 failed with "Undefined index: host").
- Configuration is read when a backup runs, so storage and database
  connections changed at runtime (`config([...])`, `Config::set()`) are used,
  also by `Artisan::call()` and by an injected `Manager`. (#78, #82, #115)
- The default `dropbox` connection uses the `Dropbox` type; `DropboxV2` is
  deprecated in core. (#129)

### Fixed
- `db:list` and the file prompt of `db:restore` work with Flysystem 2 and 3,
  and show file sizes in the right unit.
- Lumen: the shell processor is built with symfony/process 5+ (#131), and the
  `Gcs` storage type is registered (#120).
- Installing with a cached configuration no longer fails with
  `Config::__construct() ... null given`. (#121)
- Database connections with an unsupported driver (e.g. `sqlite`, `mongodb`)
  report "database type ... is not currently supported" instead of "Could not
  find field type". (#107)
- The default `sftp` connection uses port 22.
- `db:backup` without `--timestamp` no longer passes null to `date()`.

## [2.0] - 2020-04-30

Removed support for all Laravel versions below 5.5; use `^1.0` for older
versions.
