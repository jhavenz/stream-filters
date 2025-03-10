<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Container;

use ArrayAccess;
use Illuminate\Contracts\Container\Container as IlluminateContainer;
use Illuminate\Process\Factory as ProcessFactory;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Traits\ForwardsCalls;
use Jhavens\Streamfilters\Filters\CustomStreamFilter;
use Jhavens\Streamfilters\Filters\MessageBus;
use Jhavens\Streamfilters\Filters\WebSockets\WebSocketProcessor;
use Jhavens\Streamfilters\Filters\WebSockets\WebSocketStreamWrapper;
use Jhavens\Streamfilters\Routing\SimpleJsonRouter;
use Jhavens\Streamfilters\Streams\StreamFilterRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Path;

/**
 * @mixin IlluminateContainer
 */
class Container implements ArrayAccess, ContainerInterface
{
    use ForwardsCalls;

    public function __construct(protected IlluminateContainer $container)
    {
        $this->setPaths();
        $this->setBindings();
    }

    public static function getInstance(?string $basePath = null): static
    {
        $illuminateContainer = \Illuminate\Container\Container::getInstance();

        if ($basePath) {
            $illuminateContainer->instance('path', $basePath);
        }

        if (!$illuminateContainer->bound(static::class)) {
            $self = new static($illuminateContainer);

            $illuminateContainer->instance(static::class, $self);

            if (!Facade::getFacadeApplication()) {
                /** @noinspection PhpParamsInspection */
                Facade::setFacadeApplication($self);
            }

            $self->registerStreamWrappers();
        }

        return $illuminateContainer->make(static::class);
    }

    public function basePath(string ...$segments): string
    {
        return Path::join($this->container['stream_filters.path'], ...$segments);
    }

    public function srcPath(string ...$segments): string
    {
        return Path::join($this->container['stream_filters.path.src'], ...$segments);
    }

    public function testsPath(string ...$segments): string
    {
        return Path::join($this->container['stream_filters.path.tests'], ...$segments);
    }

    private function setPaths(): void
    {
        $this->container->scopedIf('path', fn () => dirname(__DIR__, 2));
        $this->container->scopedIf('stream_filters.path', fn () => dirname(__DIR__, 2));
        $this->container->scopedIf('stream_filters.path.src', fn ($app) => $app['stream_filters.path'] . '/src');
        $this->container->scopedIf('stream_filters.path.tests', fn ($app) => $app['stream_filters.path'] . '/tests');
    }

    private function setBindings(): void
    {
        $this->container->bindIf(ProcessFactory::class);
        $this->container->bindIf(CustomStreamFilter::class);
        $this->container->bindIf(WebSocketProcessor::class);

        $this->container->scopedIf(MessageBus::class);
        $this->container->scopedIf(SimpleJsonRouter::class);
        $this->container->scopedIf(StreamFilterRegistry::class);
    }

    public function get(string $id)
    {
        return $this->container->get($id);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->container->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->container->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->container->offsetSet($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->container->offsetUnset($offset);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->forwardDecoratedCallTo($this->container, $name, $arguments);
    }

    private function registerStreamWrappers(): void
    {
        WebSocketStreamWrapper::register();
    }
}
