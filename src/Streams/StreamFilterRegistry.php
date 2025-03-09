<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Streams;

use InvalidArgumentException;
use Jhavens\Streamfilters\Filters\CustomStreamFilter;

class StreamFilterRegistry
{
    private array $filters = [];

    public function register(string $name, callable $callback): static
    {
        stream_filter_register($name = "custom.{$name}", CustomStreamFilter::class);

        $this->filters[$name] = $callback;

        return $this;
    }

    public function apply(string $name, $stream): static
    {
        stream_filter_append($stream, "custom.{$name}");

        return $this;
    }

    public function filter(string $name): callable
    {
        if (!array_key_exists($name, $this->filters)) {
            throw new InvalidArgumentException("Filter {$name} not found");
        }

        return $this->filters[$name];
    }
}
