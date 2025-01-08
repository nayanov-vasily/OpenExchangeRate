<?php

namespace OpenExchangeRate\Cache;

final class SimpleCache implements \Psr\SimpleCache\CacheInterface
{
    private string $directory = '';
    private array $itemKeys;
    private array $items = [];

    /**
     * @param string $directory
     */
    public function __construct(string $directory = null)
    {
        if (is_null($directory)) {
            $directory = getcwd() . '/cache';
        }

        $this->directory = $directory;
        $this->itemKeys = scandir($this->directory);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return $default;
    }
    
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $this->items[$key] = $value;
        
        // сохранить на диск

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->items[$key]);

        // удалить с диска

        return true;
    }

    public function clear(): bool
    {
        unset($this->items);

        // удалить с диска

        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->items[$key];
        }

        if (empty($items)) {
            return $default;
        }

        return $items;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->items[$key] = $value;
        }

        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            unset($this->items[$key]);
        }

        return true;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }
}