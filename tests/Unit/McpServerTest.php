<?php

namespace Sourcecodeguy1\LaravelMcp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sourcecodeguy1\LaravelMcp\Server\McpServer;
use Sourcecodeguy1\LaravelMcp\Tools\Tool;

class McpServerTest extends TestCase
{
    private McpServer $server;

    protected function setUp(): void
    {
        $this->server = new class extends McpServer {
            public function dispatch(array $message): ?array
            {
                return $this->handleMessage($message);
            }
        };
    }

    public function test_initialize_returns_protocol_version_and_capabilities(): void
    {
        $response = $this->server->dispatch(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize', 'params' => []]);

        $this->assertSame('2.0', $response['jsonrpc']);
        $this->assertSame(1, $response['id']);
        $this->assertSame('2024-11-05', $response['result']['protocolVersion']);
        $this->assertArrayHasKey('tools', $response['result']['capabilities']);
        $this->assertSame('laravel-mcp-server', $response['result']['serverInfo']['name']);
    }

    public function test_ping_returns_empty_result(): void
    {
        $response = $this->server->dispatch(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'ping', 'params' => []]);

        $this->assertSame(2, $response['id']);
        $this->assertInstanceOf(\stdClass::class, $response['result']);
    }

    public function test_unknown_method_returns_method_not_found_error(): void
    {
        $response = $this->server->dispatch(['jsonrpc' => '2.0', 'id' => 3, 'method' => 'unknown/method', 'params' => []]);

        $this->assertSame(-32601, $response['error']['code']);
        $this->assertStringContainsString('unknown/method', $response['error']['message']);
    }

    public function test_notification_without_id_returns_null(): void
    {
        $response = $this->server->dispatch(['jsonrpc' => '2.0', 'method' => 'notifications/initialized', 'params' => []]);

        $this->assertNull($response);
    }

    public function test_tools_list_returns_registered_tools(): void
    {
        $this->server->registerTool($this->makeTool('list_routes', 'List all routes'));
        $this->server->registerTool($this->makeTool('list_models', 'List all models'));

        $response = $this->server->dispatch(['jsonrpc' => '2.0', 'id' => 4, 'method' => 'tools/list', 'params' => []]);

        $tools = $response['result']['tools'];
        $this->assertCount(2, $tools);
        $this->assertSame('list_routes', $tools[0]['name']);
        $this->assertSame('list_models', $tools[1]['name']);
    }

    public function test_tools_call_dispatches_to_correct_tool(): void
    {
        $this->server->registerTool($this->makeTool('my_tool', 'A tool', '{"result":"ok"}'));

        $response = $this->server->dispatch([
            'jsonrpc' => '2.0',
            'id'      => 5,
            'method'  => 'tools/call',
            'params'  => ['name' => 'my_tool', 'arguments' => []],
        ]);

        $this->assertSame('text', $response['result']['content'][0]['type']);
        $this->assertSame('{"result":"ok"}', $response['result']['content'][0]['text']);
        $this->assertArrayNotHasKey('isError', $response['result']);
    }

    public function test_tools_call_unknown_tool_returns_error(): void
    {
        $response = $this->server->dispatch([
            'jsonrpc' => '2.0',
            'id'      => 6,
            'method'  => 'tools/call',
            'params'  => ['name' => 'nonexistent', 'arguments' => []],
        ]);

        $this->assertSame(-32602, $response['error']['code']);
        $this->assertStringContainsString('nonexistent', $response['error']['message']);
    }

    public function test_tools_call_wraps_exception_as_error_content(): void
    {
        $failing = new class extends Tool {
            public function getName(): string        { return 'failing_tool'; }
            public function getDescription(): string { return 'Always fails'; }
            public function getInputSchema(): array  { return ['type' => 'object', 'properties' => []]; }
            public function execute(array $arguments): string { throw new \RuntimeException('something went wrong'); }
        };

        $this->server->registerTool($failing);

        $response = $this->server->dispatch([
            'jsonrpc' => '2.0',
            'id'      => 7,
            'method'  => 'tools/call',
            'params'  => ['name' => 'failing_tool', 'arguments' => []],
        ]);

        $this->assertTrue($response['result']['isError']);
        $this->assertStringContainsString('something went wrong', $response['result']['content'][0]['text']);
    }

    private function makeTool(string $name, string $description, string $output = '[]'): Tool
    {
        return new class($name, $description, $output) extends Tool {
            public function __construct(
                private string $n,
                private string $d,
                private string $o
            ) {}

            public function getName(): string        { return $this->n; }
            public function getDescription(): string { return $this->d; }
            public function getInputSchema(): array  { return ['type' => 'object', 'properties' => []]; }
            public function execute(array $arguments): string { return $this->o; }
        };
    }
}
