<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

use Illuminate\Support\Facades\Config;

class GetConfigTool extends Tool
{
    private const SENSITIVE_KEYS = [
        'password', 'secret', 'key', 'token', 'private', 'credentials', 'auth', 'api_key',
    ];

    public function getName(): string
    {
        return 'get_config';
    }

    public function getDescription(): string
    {
        return 'Read Laravel configuration values by key (e.g. "app", "database.default", "mail.mailer"). Sensitive values are redacted.';
    }

    public function getInputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'key' => [
                    'type'        => 'string',
                    'description' => 'Config key to read, e.g. "app", "database", "queue.default".',
                ],
            ],
            'required'   => ['key'],
        ];
    }

    public function execute(array $arguments): string
    {
        $key   = $arguments['key'] ?? null;
        $value = Config::get($key);

        if ($value === null) {
            return json_encode(['error' => "Config key '{$key}' not found."]);
        }

        $value = $this->redact($value, $key);

        return json_encode([$key => $value], JSON_PRETTY_PRINT);
    }

    protected function redact(mixed $value, string $key): mixed
    {
        if ($this->isSensitive($key)) {
            return '[redacted]';
        }

        if (is_array($value)) {
            return array_map(
                fn($v, $k) => [$k => $this->redact($v, (string) $k)],
                array_values($value),
                array_keys($value)
            );
        }

        return $value;
    }

    protected function isSensitive(string $key): bool
    {
        $key = strtolower($key);
        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if (str_contains($key, $sensitive)) {
                return true;
            }
        }
        return false;
    }
}
