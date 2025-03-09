<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Filters\WebSockets;

use Jhavens\Streamfilters\Streams\StreamFilterRegistry;

class WebSocketProcessor
{
    public function __construct(
        private readonly StreamFilterRegistry $registry,
    ) {
    }

    public function processMessage(string $message, array $filterNames): string
    {
        $input = fopen('php://memory', 'r+');
        $output = fopen('php://memory', 'r+');
        fwrite($input, $message);
        rewind($input);

        foreach ($filterNames as $name) {
            $this->registry->apply($name, $input);
        }

        while (!feof($input)) {
            $data = fread($input, 8192);
            if ($data !== false) {
                fwrite($output, $data);
            }
        }

        rewind($output);
        $result = stream_get_contents($output);

        fclose($input);
        fclose($output);
        return $result;
    }
}
