<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Routing;

interface MessageRouter
{
    /**
     * Register a route with a handler.
     *
     * @param string $route Route identifier (e.g., message type or pattern)
     * @param callable $handler Function to process matching messages
     * @return void
     */
    public function register(string $route, callable $handler): void;

    /**
     * Dispatch a message to its appropriate handler.
     *
     * @param string $message The raw message data
     * @return bool True if dispatched successfully, false if no route matches
     */
    public function dispatch(string $message): bool;
}
