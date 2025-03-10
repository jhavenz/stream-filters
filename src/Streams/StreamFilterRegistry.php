<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters\Streams;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Jhavens\StreamFilters\CustomStreamFilter;

class StreamFilterRegistry
{
    private array $filters = [];

    public function register(string $name, callable $callback, bool $override = false): static
    {
        $filterName = Str::start(trim($name, '.'), "custom.");
        
        if ($override || !array_key_exists($filterName, $this->filters)) {
            stream_filter_register($filterName, CustomStreamFilter::class);

            $this->filters[$filterName] = $callback;
        }

        return $this;
    }

    public function override(string $name, callable $callback): static
    {
        return $this->register($name, $callback, true);
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
