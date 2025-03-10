<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters\Tests\Architecture;

use Illuminate\Support\Facades\Process;
use Jhavens\StreamFilters\Tests\TestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Process\PhpExecutableFinder;

class DevCleanupTest extends TestCase
{
    #[Test]
    public function it_does_not_leave_commented_code ()
    {
        $command = implode(' ', [
            '"'.new PhpExecutableFinder()->find().'"',
            'vendor/bin/swiss-knife',
            'check-commented-code',
            'src',
            'tests',
            // 'config',
        ]);

        $process = Process::timeout(10)->run($command);

        if ($errorOutput = $process->errorOutput()) {
            PHPUnit::fail(str($errorOutput)->before('check-commented-code')->rtrim()->toString());
        }

        Assert::assertTrue($process->successful(), PHP_EOL
            .'[Commented Code Was Found]'
            .PHP_EOL
            .PHP_EOL
            .'Double-check the following files:'
            .PHP_EOL
            .str($process->output())->after('*.php files')->beforeLast('[ERROR]')->trim()
        );
    }
}
