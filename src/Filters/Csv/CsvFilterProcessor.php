<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Filters\Csv;

use Jhavens\Streamfilters\Filters\FilterProcessor;
use Jhavens\Streamfilters\PhpAttributes\StreamFilter;
use ReflectionClass;
use SplObserver;

class CsvFilterProcessor implements SplObserver
{
    use FilterProcessor;

    private string $trimChars = " \t\n\r\0\x0B"; // Default trim characters

    public function __construct(
    ) {
        $this->messageBus()->attach($this);
    }

    public function update(\SplSubject $subject): void
    {
        if ($subject->getMessage() === 'change_trim_chars') {
            $this->trimChars = $subject->getData() ?? $this->trimChars;
        }
    }

    #[StreamFilter('trim')]
    public function trimFilter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $data = $bucket->data;

            // Check for pattern and trigger message
            $this->messageBus()->triggerOnPattern(
                '/^SPECIAL,/',
                fn($bus) => $bus->send('change_trim_chars', ','),
                $data
            );

            $rows = array_map(fn($row) => trim($row, $this->trimChars), str_getcsv($data, escape: "\\"));
            $bucket->data = implode(',', $rows) . "\n";
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }

    private function registerFilters(): void
    {
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes(StreamFilter::class);
            if ($attributes) {
                $attr = $attributes[0]->newInstance();
                $this->registry()->register(
                    $attr->name,
                    fn ($in, $out, &$consumed, $closing) => $this->{$method->name}($in, $out, $consumed, $closing)
                );
            }
        }
    }
}
