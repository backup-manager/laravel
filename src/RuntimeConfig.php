<?php namespace BackupManager\Laravel;

use BackupManager\Config\Config;
use BackupManager\Config\ConfigFieldNotFound;
use BackupManager\Config\ConfigNotFoundForConnection;

/**
 * A core Config whose items are read from a callback on every access.
 *
 * Providers built with it see changes made to Laravel's configuration at
 * runtime (for example with Config::set()), including providers that were
 * injected into console commands when the console started.
 *
 * @internal
 */
final class RuntimeConfig extends Config {
    /**
     * @var callable
     */
    private $items;

    /**
     * @param callable $items returns the connection configs, keyed by connection name
     */
    public function __construct(callable $items) {
        parent::__construct([]);
        $this->items = $items;
    }

    /**
     * @param string $name
     * @param string|null $field
     * @return mixed
     * @throws ConfigFieldNotFound
     * @throws ConfigNotFoundForConnection
     */
    public function get($name, $field = null) {
        $items = $this->getItems();

        if ( ! array_key_exists($name, $items)) {
            throw new ConfigNotFoundForConnection("Could not find configuration for connection {$name}");
        }
        if ($field) {
            if ( ! array_key_exists($field, $items[$name])) {
                throw new ConfigFieldNotFound("Could not find field {$field} in configuration for connection type {$name}");
            }
            return $items[$name][$field];
        }
        return $items[$name];
    }

    /**
     * Connection configs only; scalar settings such as command-extra-params
     * are not connections.
     *
     * @return array
     */
    public function getItems() {
        return array_filter((array) call_user_func($this->items), 'is_array');
    }
}
