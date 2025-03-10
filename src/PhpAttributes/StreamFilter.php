<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters\PhpAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class StreamFilter
{
    public function __construct(
        public string $name,
    ) {}
}
