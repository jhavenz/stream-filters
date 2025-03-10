<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Filters;

use Jhavens\Streamfilters\Container\Container;
use Jhavens\Streamfilters\Routing\SimpleJsonRouter;
use Jhavens\Streamfilters\Streams\StreamFilterRegistry;

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
