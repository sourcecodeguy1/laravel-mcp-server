<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

use Illuminate\Support\Facades\DB;

class GetMigrationsTool extends Tool
{
    public function getName(): string
    {
        return 'get_migrations';
    }

    public function getDescription(): string
    {
        return 'Get migration status — which migrations have been run and which are still pending.';
    }

    public function getInputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => new \stdClass(),
        ];
    }

    public function execute(array $arguments): string
    {
        try {
            $ran      = DB::table('migrations')->orderBy('batch')->get();
            $ranNames = $ran->pluck('migration')->toArray();

            $pending = collect(glob(database_path('migrations/*.php')))
                ->map(fn($f) => pathinfo($f, PATHINFO_FILENAME))
                ->reject(fn($name) => in_array($name, $ranNames))
                ->values()
                ->toArray();

            return json_encode([
                'ran'           => $ran->map(fn($m) => ['migration' => $m->migration, 'batch' => $m->batch])->toArray(),
                'pending'       => $pending,
                'total_ran'     => $ran->count(),
                'total_pending' => count($pending),
            ], JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            return json_encode(['error' => 'Could not read migrations: ' . $e->getMessage()]);
        }
    }
}
