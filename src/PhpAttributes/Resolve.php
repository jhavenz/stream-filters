<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters\PhpAttributes;


use Attribute;
use Illuminate\Contracts\Container\ContextualAttribute;
use Jhavens\StreamFilters\Container\Container;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class Resolve implements ContextualAttribute
{
    public function __construct(
        public string $binding,
        public array $args = [],
    ) {
        //
    }

    /**
     * Automatically called by Laravel when resolving the target
     */
    public function resolve(self $self, Container $ioc): mixed
    {
        return $ioc->make($self->binding, $self->args);
    }

    /**
     * Automatically called by Laravel when resolving the target
     */
    public function after(self $self, object $instance, Container $ioc)
    {
        //
    }
}
