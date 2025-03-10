<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Filters\WebSockets;

use Jhavens\Streamfilters\Filters\FilterProcessor;
use RuntimeException;

class WebSocketProcessor
{
    use FilterProcessor;

    /**
     * Process WebSocket messages with filters and route them.
     *
     * @param string $wsUrl WebSocket server URL
     * @param array $filterNames Filters to apply to incoming messages
     * @param callable|null $defaultHandler Fallback handler for unrouted messages
     * @return void
     */
    public function process(string $wsUrl, array $filterNames, ?callable $defaultHandler = null): void
    {
        // Placeholder for stream opening (replace with WebSocket stream wrapper later)
        $stream = fopen($wsUrl, 'r+'); // Assume websocket:// protocol once implemented
        if ($stream === false) {
            throw new RuntimeException("Failed to open WebSocket stream: $wsUrl");
        }

        // Apply filters
        foreach ($filterNames as $name) {
            $this->registry()->apply($name, $stream);
        }

        // Process messages
        while (!feof($stream)) {
            $data = fread($stream, 8192);
            if ($data !== false && strlen($data) > 0) {
                if (!$this->router()->dispatch($data) && $defaultHandler !== null) {
                    $defaultHandler($data); // Handle unrouted messages
                }
            }
        }

        fclose($stream);
    }

    /**
     * Send a message through the WebSocket stream.
     *
     * @param resource $stream WebSocket stream
     * @param string $message Message to send
     * @return void
     */
    public function sendMessage($stream, string $message): void
    {
        fwrite($stream, $message);
    }
}
