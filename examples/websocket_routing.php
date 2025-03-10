<?php

require __DIR__ . '/../vendor/autoload.php';

use Jhavens\Streamfilters\Container\Container;
use Jhavens\Streamfilters\Filters\WebSockets\WebSocketProcessor;
use Jhavens\Streamfilters\Routing\SimpleJsonRouter;

$app = Container::getInstance(dirname(__DIR__));

$router = $app->make(SimpleJsonRouter::class);
$processor = $app->make(WebSocketProcessor::class);

// Register routes
$router->register('chat', function (string $message) {
    echo "Chat message received: $message\n";
});

$router->register('status', function (string $message) {
    echo "Status update received: $message\n";
});

// Default handler for unrouted messages
$defaultHandler = function (string $message) {
    echo "Unrouted message: $message\n";
};

// Process WebSocket stream with filters and routing
$filters = ['json_uppercase']; // Assume this filter exists from previous response
$processor->process('websocket://localhost:8080', $filters, $defaultHandler);
