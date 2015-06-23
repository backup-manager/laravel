# Laravel Driver for the Database Backup Manager

This package pulls in the framework agnostic [Backup Manager](https://github.com/backup-manager/backup-manager) and provides seamless integration with **Laravel**. 

Mitchell has put together a [video tour](https://www.youtube.com/watch?v=vWXy0R8OavM) of Laravel integration, to give you an idea what is possible with this package.

> Note: This package is for Laravel integration only. For information about the base package please see [the base package repository](https://github.com/backup-manager/backup-manager).

### Table of Contents

- [Stability Notice](#stability-notice)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Contribution Guidelines](#contribution-guidelines)
- [Maintainers](#maintainers)
- [License](#license)

### Stability Notice

It's stable enough, you'll need to understand filesystem permissions.

This package is actively being developed and we would like to get feedback to improve it. [Please feel free to submit feedback.](https://github.com/backup-manager/laravel/issues/new)

### Requirements

- PHP 5.5
- Laravel
- MySQL support requires `mysqldump` and `mysql` command-line binaries
- PostgreSQL support requires `pg_dump` and `psql` command-line binaries
- Gzip support requires `gzip` and `gunzip` command-line binaries

### Installation

**Composer**

Run the following to include this via Composer

```shell
composer require backup-manager/laravel
```

Then, you'll need to select the appropriate packages for the adapters that you want to use.

```shell
# to support s3
composer require league/flysystem-aws-s3-v2

# to support dropbox
composer require league/flysystem-dropbox

# to support rackspace
composer require league/flysystem-rackspace

# to support sftp
composer require league/flysystem-sftp
```

#### Laravel 4 Configuration

To install into a Laravel 4 project, first do the composer install then add the following class to your config/app.php service providers list.

```php
BackupManager\Laravel\Laravel4ServiceProvider::class,
```

Copy the `vendor/backup-manager/laravel/config/backup-manager.php` file to `app/config/backup-manager.php` and configure it to suit your needs.

#### Laravel 5 Configuration

To install into a Laravel project, first do the composer install then add the following class to your config/app.php service providers list.

```php
BackupManager\Laravel\Laravel5ServiceProvider::class,
```

Publish the storage configuration file.

```php 
php artisan vendor:publish --provider="BackupManager\Laravel\Laravel5BackupManagerServiceProvider"
```

The Backup Manager will make use of Laravel's database configuration. But, it won't know about any connections that might be tied to other environments, so it can be best to just list multiple connections in the `config/database.php` file.

**IoC Resolution**

`BackupManager\Manager` can be automatically resolved through constructor injection thanks to Laravel's IoC container.

```php
use BackupManager\Manager;

public function __construct(Manager $manager) {
    $this->manager = $manager;
}
```

It can also be resolved manually from the container.

```php
$manager = App::make(\BackupManager\Manager::class);
```

**Artisan Commands**

There are three commands available `db:backup`, `db:restore` and `db:list`.

All will prompt you with simple questions to successfully execute the command.

### Contribution Guidelines

We recommend using the vagrant configuration supplied with this package for development and contribution. Simply install VirtualBox, Vagrant, and Ansible then run `vagrant up` in the root folder. A virtualmachine specifically designed for development of the package will be built and launched for you.

When contributing please consider the following guidelines:

- please conform to the code style of the project, it's essentially PSR-2 with a few differences.
    1. The NOT operator when next to parenthesis should be surrounded by a single space. `if ( ! is_null(...)) {`.
    2. Interfaces should NOT be suffixed with `Interface`, Traits should NOT be suffixed with `Trait`.
- All methods and classes must contain docblocks.
- Ensure that you submit tests that have minimal 100% coverage.
- When planning a pull-request to add new functionality, it may be wise to [submit a proposal](https://github.com/backup-manager/laravel/issues/new) to ensure compatibility with the project's goals.

### Maintainers

This package is maintained by [Shawn McCool](http://shawnmc.cool) and [Mitchell van Wijngaarden](http://kooding.nl).

### License

This package is licensed under the [MIT license](https://github.com/backup-manager/laravel/blob/master/LICENSE).
