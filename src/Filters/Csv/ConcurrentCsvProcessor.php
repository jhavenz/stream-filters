<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Filters\Csv;

use RuntimeException;

class ConcurrentCsvProcessor extends CsvFilterProcessor
{
    public function process(array $inputFiles, string $outputFile, array $filterNames): void
    {
        // Open the output stream
        $output = fopen($outputFile, 'w');
        if ($output === false) {
            throw new RuntimeException("Failed to open output file: $outputFile");
        }

        // Open all input streams and set them to non-blocking
        $inputs = array_map(function ($file) {
            $stream = fopen($file, 'r');
            if ($stream === false) {
                throw new RuntimeException("Failed to open input file: $file");
            }
            stream_set_blocking($stream, false);
            return $stream;
        }, $inputFiles);

        // Apply filters to each input stream
        foreach ($filterNames as $name) {
            foreach ($inputs as $input) {
                $this->registry()->apply($name, $input);
            }
        }

        $streams = $inputs;
        $write = [$output];
        $except = [];

        // Process streams until all are exhausted
        while (!empty($streams)) {
            $readable = $streams;
            $writable = $write;
            $result = stream_select($readable, $writable, $except, 0, 200000); // 200ms timeout

            if ($result === false) {
                $this->closeStreams($inputs, $output);
                throw new RuntimeException('stream_select failed');
            }

            foreach ($readable as $stream) {
                $data = fread($stream, 8192);
                if ($data === false || strlen($data) === 0) {
                    fclose($stream);
                    $streams = array_filter($streams, fn($s) => $s !== $stream);
                    continue;
                }
                fwrite($output, $data);
            }
        }

        $this->closeStreams($inputs, $output);
    }

    private function closeStreams(array $inputs, $output): void
    {
        foreach ($inputs as $stream) {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
        if (is_resource($output)) {
            fclose($output);
        }
    }
}
