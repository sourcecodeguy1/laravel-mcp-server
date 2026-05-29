<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

use Illuminate\Support\Facades\DB;

class ListJobsTool extends Tool
{
    public function getName(): string
    {
        return 'list_jobs';
    }

    public function getDescription(): string
    {
        return 'List pending jobs and failed jobs from the queue. Useful for debugging background processing issues.';
    }

    public function getInputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'type' => [
                    'type'        => 'string',
                    'description' => 'Which jobs to return: "pending", "failed", or "all". Defaults to "all".',
                ],
                'limit' => [
                    'type'        => 'integer',
                    'description' => 'Maximum number of records to return. Defaults to 20.',
                ],
            ],
        ];
    }

    public function execute(array $arguments): string
    {
        $type  = $arguments['type'] ?? 'all';
        $limit = max(1, (int) ($arguments['limit'] ?? 20));
        $result = [];

        if (in_array($type, ['pending', 'all'])) {
            try {
                $result['pending'] = DB::table('jobs')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get(['id', 'queue', 'payload', 'attempts', 'created_at'])
                    ->map(fn($job) => [
                        'id'         => $job->id,
                        'queue'      => $job->queue,
                        'job'        => json_decode($job->payload, true)['displayName'] ?? 'Unknown',
                        'attempts'   => $job->attempts,
                        'created_at' => $job->created_at,
                    ])->toArray();
            } catch (\Throwable) {
                $result['pending'] = 'jobs table not found — queue driver may not use database';
            }
        }

        if (in_array($type, ['failed', 'all'])) {
            try {
                $result['failed'] = DB::table('failed_jobs')
                    ->orderBy('failed_at', 'desc')
                    ->limit($limit)
                    ->get(['id', 'queue', 'payload', 'exception', 'failed_at'])
                    ->map(fn($job) => [
                        'id'         => $job->id,
                        'queue'      => $job->queue,
                        'job'        => json_decode($job->payload, true)['displayName'] ?? 'Unknown',
                        'exception'  => substr($job->exception, 0, 200),
                        'failed_at'  => $job->failed_at,
                    ])->toArray();
            } catch (\Throwable) {
                $result['failed'] = 'failed_jobs table not found';
            }
        }

        return json_encode($result, JSON_PRETTY_PRINT);
    }
}
