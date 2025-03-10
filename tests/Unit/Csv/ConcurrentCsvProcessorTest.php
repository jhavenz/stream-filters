<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Tests\Unit\Csv;

use Jhavens\Streamfilters\Filters\Csv\ConcurrentCsvProcessor;
use Jhavens\Streamfilters\Filters\MessageBus;
use Jhavens\Streamfilters\Tests\TestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;

class ConcurrentCsvProcessorTest extends TestCase
{

    #[Test]
    public function it_uses_trim_stream_filter ()
    {
        $processor = $this->app[ConcurrentCsvProcessor::class];

        $inputFile = $this->csvPath('input.csv');
        $outputFile = $this->outputDir('output.csv');
        $processor->process($inputFile, $outputFile, ['trim']);

        $expectedFileContent = "name,age,city
        Alice,30,New York
        Bob,25,Los Angeles
        ";

        Assert::assertStringEqualsFileCanonicalizing(
            $outputFile,
            $expectedFileContent,
        );
    }

    #[Test]
    public function it_updates_trim_chars_dynamically()
    {
        $messageBus = $this->app[MessageBus::class];
        $processor = $this->app[ConcurrentCsvProcessor::class];

        $inputFile = $this->csvPath('input_with_commas.csv');
        $outputFile = $this->outputDir('output_dynamic.csv');

        $messageBus->send('change_trim_chars', ',');
        $processor->process($inputFile, $outputFile, ['trim']);

        $expected = "name,age,city\nAlice,30,New York\nBob,25,Los Angeles\n";
        Assert::assertStringEqualsFileCanonicalizing($outputFile, $expected);
    }

    #[Test]
    public function it_processes_input_from_multiple_csvs ()
    {
        $processor = $this->app[ConcurrentCsvProcessor::class];

        $processor->process(
            [$this->csvPath('input1.csv'), $this->csvPath('input2.csv')],
            $this->csvPath('output.csv'),
            ['trim', 'uppercase']
        );

        dump(file_get_contents($this->csvPath('output.csv')));
    }
}
