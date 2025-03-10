<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters\Csv;

use Jhavens\StreamFilters\FilterProcessor;
use Jhavens\StreamFilters\IStreamProcessor;
use Jhavens\StreamFilters\PhpAttributes\StreamFilter;
use SplObserver;
use SplSubject;

class CsvFilterProcessor implements SplObserver, IStreamProcessor
{
    use FilterProcessor;

    private string $trimChars = " \t\n\r\0\x0B"; // Default trim characters

    private array $headers = [];

    public function __construct(
    ) {
        $this->messageBus()->attach($this);
    }

    public function update(SplSubject $subject): void
    {
        if ($subject->getMessage() === 'change_trim_chars') {
            $this->trimChars = $subject->getData() ?? $this->trimChars;
        }
    }

    // headers filter
    #[StreamFilter('headers')]
    public function headersFilter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $data = $bucket->data;
            // if headers are set, remove them from the data
            // then add a final task to the stream to prepend the headers
            preg_match('/[^name\s?+,age\s?+,city\s?+\n$]/mi', $data, $matches);
            if ($this->headers) {
                $lineContent = implode(",", $this->headers);
                $lineContentEscaped = preg_quote($lineContent, "/");
                $withoutHeaders = preg_replace("/^{$lineContentEscaped}\n/im", '', $data);

                $bucket->data = $withoutHeaders;
                $consumed += $bucket->datalen;
                stream_bucket_append($out, $bucket);
            }
        }

        return PSFS_PASS_ON;
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

    #[StreamFilter('uppercase')]
    public function uppercaseFilter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket->data = strtoupper($bucket->data);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }

    public function withHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }
}
