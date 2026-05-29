<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

class GetLogsTool extends Tool
{
    public function getName(): string
    {
        return 'get_logs';
    }

    public function getDescription(): string
    {
        return 'Read the last N lines from storage/logs/laravel.log. Useful for diagnosing recent errors and exceptions.';
    }

    public function getInputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'lines' => [
                    'type'        => 'integer',
                    'description' => 'Number of lines to return from the end of the log. Defaults to 50.',
                ],
                'filter' => [
                    'type'        => 'string',
                    'description' => 'Only return lines containing this string (e.g. "ERROR", "Exception").',
                ],
            ],
        ];
    }

    public function execute(array $arguments): string
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return json_encode(['error' => 'Log file not found at ' . $logPath]);
        }

        $lines  = max(1, (int) ($arguments['lines'] ?? 50));
        $filter = $arguments['filter'] ?? null;

        $file    = new \SplFileObject($logPath, 'r');
        $file->seek(PHP_INT_MAX);
        $total   = $file->key();

        $start   = max(0, $total - $lines);
        $results = [];

        $file->seek($start);
        while (!$file->eof()) {
            $line = rtrim($file->current(), "\n");
            if ($line !== '' && ($filter === null || str_contains($line, $filter))) {
                $results[] = $line;
            }
            $file->next();
        }

        return json_encode(['lines' => $results, 'total_lines_in_file' => $total], JSON_PRETTY_PRINT);
    }
}
