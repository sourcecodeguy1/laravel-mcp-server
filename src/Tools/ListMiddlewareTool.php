<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

class ListMiddlewareTool extends Tool
{
    public function getName(): string
    {
        return 'list_middleware';
    }

    public function getDescription(): string
    {
        return 'List all registered middleware — global middleware, named aliases, and middleware groups (web, api, etc.).';
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
        $router = app('router');

        $result = [
            'global'  => $this->getGlobalMiddleware(),
            'groups'  => $router->getMiddlewareGroups(),
            'aliases' => $router->getMiddleware(),
        ];

        return json_encode($result, JSON_PRETTY_PRINT);
    }

    private function getGlobalMiddleware(): array
    {
        try {
            $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
            $ref = new \ReflectionProperty($kernel, 'middleware');
            $ref->setAccessible(true);
            return array_values($ref->getValue($kernel));
        } catch (\Throwable) {
            return [];
        }
    }
}
