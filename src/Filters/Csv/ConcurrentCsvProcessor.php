<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Filters\Csv;

use RuntimeException;

class ConcurrentCsvProcessor extends CsvFilterProcessor
{
    public function process(string $inputFile, string $outputFile, array $filterNames): void
    {
        $input = fopen($inputFile, 'r');
        $output = fopen($outputFile, 'w');
        stream_set_blocking($input, false);

        foreach ($filterNames as $name) {
            $this->registry()->apply($name, $input);
        }

        $chunkSize = 8192;
        $streams = [$input];
        $write = [$output];
        $except = [];

        while (!feof($input)) {
            $readable = $streams;
            $writable = $write;
            if (stream_select($readable, $writable, $except, 0, 200000) === false) {
                throw new RuntimeException('Stream select failed');
            }

            foreach ($readable as $stream) {
                $data = fread($stream, $chunkSize);
                if ($data !== false && strlen($data) > 0) {
                    fwrite($output, $data);
                }
            }
        }

        fclose($input);
        fclose($output);
    }
}
