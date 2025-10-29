<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Container;

class Container
{
    private array $services = [];
    private array $instances = [];

    public function set(string $name, callable $definition): void
    {
        $this->services[$name] = $definition;
    }

    public function get(string $name): mixed
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        if (!isset($this->services[$name])) {
            throw new \Exception("Service '{$name}' not found");
        }

        $this->instances[$name] = $this->services[$name]($this);
        return $this->instances[$name];
    }

    public function singleton(string $name, callable $definition): void
    {
        $this->set($name, $definition);
    }

    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }
}