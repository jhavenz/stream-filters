<?php

declare(strict_types=1);

namespace Architecture;

use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\Attributes\Test;

class DevCleanupTest
{
    #[Test]
    public function it_does_not_leave_commented_code ()
    {
        $command = implode(' ', [
            'php',
            'vendor/bin/swiss-knife',
            'check-commented-code',
            'src',
            'tests',
            'config',
            'resources',
        ]);

        $process = Process::timeout(10)->run($command);

        if ($errorOutput = $process->errorOutput()) {
            PHPUnit::fail(str($errorOutput)->before('check-commented-code')->rtrim()->toString());
        }

        expect($process->successful())->toBeTrue(
            PHP_EOL
            .'[Commented Code Was Found]'
            .PHP_EOL
            .PHP_EOL
            .'Double-check the following files:'
            .PHP_EOL
            .str($process->output())->after('*.php files')->beforeLast('[ERROR]')->trim()
        );
    }
}
