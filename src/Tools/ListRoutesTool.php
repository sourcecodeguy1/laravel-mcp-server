<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

use Illuminate\Support\Facades\Route;

class ListRoutesTool extends Tool
{
    public function getName(): string
    {
        return 'list_routes';
    }

    public function getDescription(): string
    {
        return 'List all registered routes in the Laravel application. Optionally filter by HTTP method or URI pattern.';
    }

    public function getInputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'method' => [
                    'type'        => 'string',
                    'description' => 'Filter by HTTP method: GET, POST, PUT, PATCH, DELETE. Leave empty for all.',
                ],
                'uri_filter' => [
                    'type'        => 'string',
                    'description' => 'Filter routes whose URI contains this string.',
                ],
            ],
        ];
    }

    public function execute(array $arguments): string
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        if (!empty($arguments['method'])) {
            $method = strtoupper($arguments['method']);
            $routes = $routes->filter(fn($r) => in_array($method, $r->methods()));
        }

        if (!empty($arguments['uri_filter'])) {
            $filter = $arguments['uri_filter'];
            $routes = $routes->filter(fn($r) => str_contains($r->uri(), $filter));
        }

        $result = $routes->map(fn($r) => [
            'methods'    => $r->methods(),
            'uri'        => $r->uri(),
            'name'       => $r->getName(),
            'action'     => $r->getActionName(),
            'middleware' => $r->gatherMiddleware(),
        ])->values()->toArray();

        return json_encode($result, JSON_PRETTY_PRINT);
    }
}
