<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters;

class SimpleJsonRouter
{
    private(set) array $routes = [];

    public function register(string $route, callable $handler): void
    {
        $this->routes[$route] = $handler;
    }

    public function dispatch(string $message): bool
    {
        $data = json_decode($message, true);
        if (!is_array($data) || !isset($data['type'])) {
            return false; // Invalid or unroutable message
        }

        $route = $data['type'];
        if (isset($this->routes[$route])) {
            ($this->routes[$route])($message);
            return true;
        }

        return false; // No matching route
    }
}
