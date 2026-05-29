<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

use Illuminate\Support\Facades\Artisan;

class ListCommandsTool extends Tool
{
    public function getName(): string
    {
        return 'list_commands';
    }

    public function getDescription(): string
    {
        return 'List all registered Artisan commands, including custom application commands.';
    }

    public function getInputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'filter' => [
                    'type'        => 'string',
                    'description' => 'Only return commands whose name contains this string.',
                ],
            ],
        ];
    }

    public function execute(array $arguments): string
    {
        $filter   = $arguments['filter'] ?? null;
        $commands = Artisan::all();

        $result = collect($commands)
            ->when($filter, fn($c) => $c->filter(fn($cmd, $name) => str_contains($name, $filter)))
            ->map(fn($cmd) => [
                'name'        => $cmd->getName(),
                'description' => $cmd->getDescription(),
            ])
            ->values()
            ->toArray();

        return json_encode($result, JSON_PRETTY_PRINT);
    }
}
