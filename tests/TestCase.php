<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Tests;

use Jhavens\Streamfilters\Container\Container;
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
        return tap($this->app->testsPath('csv', $file), function ($path) {
            assert(file_exists($path), "File not found at path: {$path}");
        });
    }

    protected function outputDir(string $file): string
    {
        return $this->app->testsPath('output', $file);
    }
}
