<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Filters\Csv;

use Jhavens\Streamfilters\Filters\MessageBus;
use Jhavens\Streamfilters\PhpAttributes\StreamFilter;
use Jhavens\Streamfilters\Streams\StreamFilterRegistry;
use ReflectionClass;
use SplObserver;

class CsvFilterProcessor implements SplObserver
{
    private string $trimChars = " \t\n\r\0\x0B"; // Default trim characters

    public function __construct(
        private readonly StreamFilterRegistry $registry,
        private readonly MessageBus $messageBus,
    ) {
        $this->messageBus->attach($this); // Register as observer
        $this->registerFilters();
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
            $data = array_map(fn($cell) => trim($cell, $this->trimChars), str_getcsv($bucket->data, escape: "\\"));
            $bucket->data = implode(',', $data) . "\n";
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }

    public function registry(): StreamFilterRegistry
    {
        return $this->registry;
    }

    private function registerFilters(): void
    {
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes(StreamFilter::class);
            if ($attributes) {
                $attr = $attributes[0]->newInstance();
                $this->registry->register(
                    $attr->name,
                    fn ($in, $out, &$consumed, $closing) => $this->{$method->name}($in, $out, $consumed, $closing)
                );
            }
        }
    }
}
