<?php

declare(strict_types=1);

namespace Jhavens\StreamFilters\Csv;

use Fiber;
use RuntimeException;

class ConcurrentCsvProcessor extends CsvFilterProcessor
{
    public function process(array $inputFiles, string $outputFile, array $filterNames): static
    {
        $output = fopen($outputFile, 'w');
        if ($output === false) {
            throw new RuntimeException("Failed to open output file: $outputFile");
        }

        $fibers = [];
        $errors = []; // Collect exceptions from Fibers
        $headers = [];

        // Setup phase: Open streams and create Fibers
        foreach ($inputFiles as $i => $file) {
            $stream = fopen($file, 'r');
            if ($stream === false) {
                fclose($output);
                throw new RuntimeException("Failed to open input file: $file");
            }

            foreach ($filterNames as $name) {
                $this->registry()->apply($name, $stream);
            }

            $fibers[] = new Fiber(function () use ($stream, $output, &$errors, &$headers) {
                try {
                    while (!feof($stream)) {
                        $data = fread($stream, 8192);
                        if ($data === false) {
                            throw new RuntimeException("Failed to read from stream");
                        }
                        $lines = explode("\n", $data);
                        if (empty($headers)) {
                            $headers = array_shift($lines);
                        } elseif ($headers === $lines[0]) {
                            array_shift($lines);
                        }

                        fwrite($output, $data);

                        Fiber::suspend();
                    }
                } finally {
                    fclose($stream);
                }
            });
        }

        // Execution phase: Start and manage Fibers
        foreach ($fibers as $fiber) {
            $fiber->start();
        }

        while (count($fibers) > 0) {
            foreach ($fibers as $key => $fiber) {
                if ($fiber->isTerminated()) {
                    unset($fibers[$key]);
                } else {
                    $fiber->resume();
                }
            }
            usleep(1000); // Brief pause to prevent tight looping
        }

        $this->done($output);

        return $this;
    }

    public function process2(array $inputFiles, string $outputFile, array $filterNames): void
    {
        // Open the output stream
        $output = fopen($outputFile, 'w');
        if ($output === false) {
            throw new RuntimeException("Failed to open output file: $outputFile");
        }

        $inputs = array_map(function ($file) {
            $stream = fopen($file, 'r');
            if ($stream === false) {
                throw new RuntimeException("Failed to open input file: $file");
            }
            stream_set_blocking($stream, false);
            return $stream;
        }, $inputFiles);

        foreach ($filterNames as $name) {
            foreach ($inputs as $input) {
                $this->registry()->apply($name, $input);
            }
        }

        $streams = $inputs;
        while (!empty($streams)) {
            $readable = $streams;
            $writable = [$output];
            $except = [];

            $result = stream_select($readable, $writable, $except, 0, 200000);

            if ($result === false) {
                $this->closeStreams($inputs, $output);
                throw new RuntimeException('stream_select failed');
            }

            if ($result === 0) {
                continue; // No streams ready, try again
            }

            foreach ($readable as $stream) {
                $data = fread($stream, 8192);
                if ($data === false) {
                    fclose($stream);
                    $streams = array_filter($streams, fn($s) => $s !== $stream);
                    continue;
                }


                if (strlen($data) > 0) {
                    fwrite($output, $data);
                }

                if (feof($stream)) {
                    fclose($stream);
                    $streams = array_filter($streams, fn($s) => $s !== $stream);
                }
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
