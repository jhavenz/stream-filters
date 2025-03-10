<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters;

use Jhavens\StreamFilters\Container\Container;
use Jhavens\StreamFilters\Streams\StreamFilterRegistry;

trait FilterProcessor
{
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
}
