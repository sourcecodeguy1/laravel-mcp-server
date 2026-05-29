<?php

namespace Sourcecodeguy1\LaravelMcp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sourcecodeguy1\LaravelMcp\Tools\GetConfigTool;

class GetConfigToolTest extends TestCase
{
    private GetConfigTool $tool;

    protected function setUp(): void
    {
        $this->tool = new class extends GetConfigTool {
            public function redactPublic(mixed $value, string $key): mixed
            {
                return $this->redact($value, $key);
            }

            public function isSensitivePublic(string $key): bool
            {
                return $this->isSensitive($key);
            }
        };
    }

    public function test_password_key_is_sensitive(): void
    {
        $this->assertTrue($this->tool->isSensitivePublic('password'));
    }

    public function test_secret_key_is_sensitive(): void
    {
        $this->assertTrue($this->tool->isSensitivePublic('app_secret'));
    }

    public function test_api_key_is_sensitive(): void
    {
        $this->assertTrue($this->tool->isSensitivePublic('stripe_api_key'));
    }

    public function test_token_key_is_sensitive(): void
    {
        $this->assertTrue($this->tool->isSensitivePublic('access_token'));
    }

    public function test_non_sensitive_key_is_not_sensitive(): void
    {
        $this->assertFalse($this->tool->isSensitivePublic('app_name'));
    }

    public function test_sensitive_value_is_redacted(): void
    {
        $this->assertSame('[redacted]', $this->tool->redactPublic('super-secret', 'password'));
    }

    public function test_non_sensitive_value_is_returned_as_is(): void
    {
        $this->assertSame('production', $this->tool->redactPublic('production', 'env'));
    }
}
