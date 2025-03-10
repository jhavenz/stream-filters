<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters;


/**
 * Marker interface for resolving stream filters.
 * Any method marked with the #[StreamFilter] attribute 
 * will be registered.
 */
interface IStreamProcessor
{
    public function registerFilters();   
}
