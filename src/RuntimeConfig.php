<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

use BackupManager\Config\Config;
use BackupManager\Config\ConfigFieldNotFound;
use BackupManager\Config\ConfigNotFoundForConnection;
use Closure;

/**
 * A core Config whose entries are read from a callback on every access.
 *
 * Providers built with it see changes made to Laravel's configuration at
 * runtime (config([...]), Config::set()), including providers that an
 * injected Manager already holds.
 *
 * @internal
 */
final class RuntimeConfig extends Config
{
    /** @var Closure(): array<mixed> */
    private Closure $entries;

    /**
     * @param Closure(): array<mixed> $entries returns connection configs keyed by connection name
     */
    public static function fromCallback(Closure $entries): self
    {
        $config = new self([]);
        $config->entries = $entries;

        return $config;
    }

    #[\Override]
    public function get(string $name, ?string $field = null): mixed
    {
        $entries = $this->getItems();

        if (!array_key_exists($name, $entries)) {
            throw new ConfigNotFoundForConnection("Could not find configuration for connection {$name}");
        }
        if ($field === null || $field === '') {
            return $entries[$name];
        }
        if (!array_key_exists($field, $entries[$name])) {
            throw new ConfigFieldNotFound("Could not find field {$field} in configuration for connection type {$name}");
        }

        return $entries[$name][$field];
    }

    /**
     * Connection configs only; scalar settings such as command-extra-params
     * are not connections.
     *
     * @return array<string, array<string, mixed>>
     */
    #[\Override]
    public function getItems(): array
    {
        $entries = [];
        foreach (($this->entries)() as $name => $entry) {
            if (is_array($entry)) {
                /** @var array<string, mixed> $entry */
                $entries[(string) $name] = $entry;
            }
        }

        return $entries;
    }
}
