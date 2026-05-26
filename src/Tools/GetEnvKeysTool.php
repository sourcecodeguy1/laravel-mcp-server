<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

class GetEnvKeysTool extends Tool
{
    public function getName(): string
    {
        return 'get_env_keys';
    }

    public function getDescription(): string
    {
        return 'Get all environment variable key names from the .env file. Returns keys only — never values — for security.';
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
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            return json_encode(['error' => '.env file not found']);
        }

        $keys = [];

        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || !str_contains($line, '=')) continue;

            $key = trim(explode('=', $line, 2)[0]);
            if ($key) $keys[] = $key;
        }

        return json_encode(['keys' => $keys, 'count' => count($keys)], JSON_PRETTY_PRINT);
    }
}
