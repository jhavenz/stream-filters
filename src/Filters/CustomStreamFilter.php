<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Filters;

use Illuminate\Container\Container;
use Jhavens\Streamfilters\Streams\StreamFilterRegistry;
use php_user_filter;

class CustomStreamFilter extends php_user_filter
{
    public function filter($in, $out, &$consumed, $closing): int
    {
        $registry = Container::getInstance()->make(StreamFilterRegistry::class);

        return $registry->filter($this->filtername)(...func_get_args());
    }
}
