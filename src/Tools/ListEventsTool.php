<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

use Illuminate\Support\Facades\Event;

class ListEventsTool extends Tool
{
    public function getName(): string
    {
        return 'list_events';
    }

    public function getDescription(): string
    {
        return 'List all registered events and their listeners in the Laravel application.';
    }

    public function getInputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [],
        ];
    }

    public function execute(array $arguments): string
    {
        $listeners = Event::getRawListeners();

        $result = [];
        foreach ($listeners as $event => $eventListeners) {
            $result[$event] = collect($eventListeners)->map(function ($listener) {
                if (is_string($listener)) {
                    return $listener;
                }
                if (is_array($listener)) {
                    return implode('@', array_map(fn($l) => is_object($l) ? get_class($l) : $l, $listener));
                }
                return 'Closure';
            })->values()->toArray();
        }

        ksort($result);

        return json_encode($result, JSON_PRETTY_PRINT);
    }
}
