<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters\Tests;

use Jhavens\StreamFilters\Container\Container;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    protected Container $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = Container::getInstance();
    }

    protected function csvPath(string $file): string
    {
        return $this->app->testsPath('csv', $file);
    }

    protected function outputDir(string $file): string
    {
        return $this->app->testsPath('output', $file);
    }
}
