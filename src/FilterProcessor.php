<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters;

use Jhavens\StreamFilters\Container\Container;
use Jhavens\StreamFilters\Streams\StreamFilterRegistry;

trait FilterProcessor
{
    private $output = null;

    private array $postProcessors = [];

    protected function registry()
    {
        return Container::getInstance()->make(StreamFilterRegistry::class);
    }

    protected function messageBus()
    {
        return Container::getInstance()->make(MessageBus::class);
    }

    protected function router(): SimpleJsonRouter
    {
        return Container::getInstance()->make(SimpleJsonRouter::class);
    }

    protected function done(mixed $output): void
    {
        $this->output = $output;

        foreach ($this->postProcessors as $postProcessor) {
            $postProcessor($output);
        }
    }

    public function then(callable $postProcessor): static
    {
        if ($this->output) {
            $postProcessor($this->output);
        } else {
            $this->postProcessors[] = $postProcessor;
        }

        return $this;
    }

    public function __destruct()
    {
        if (is_null($this->output)) {
            trigger_error("'done' method was not called after processing", E_USER_WARNING);
            return;
        }

        if (is_resource($this->output)) {
            fclose($this->output);
        } else {
            unset($this->output);
        }
    }
}
