<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters\Csv\Filters;

use Jhavens\StreamFilters\IStreamProcessor;
use Jhavens\StreamFilters\MessageBus;
use Jhavens\StreamFilters\PhpAttributes\Resolve;
use Jhavens\StreamFilters\PhpAttributes\StreamFilter;

class TrimFilter implements IStreamProcessor
{
    private string $trimChars = " \t\n\r\0\x0B"; // Default trim characters

    public function __construct(
        #[Resolve(MessageBus::class)]
        private MessageBus $messageBus
    ) {}

    #[StreamFilter('trim')]
    public function __invoke($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $data = $bucket->data;

            // Check for pattern and trigger message
            $this->messageBus->triggerOnPattern(
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
}
