<?php

namespace Sourcecodeguy1\LaravelMcp\Server;

use Sourcecodeguy1\LaravelMcp\Tools\Tool;

class McpServer
{
    protected array $tools = [];

    public function registerTool(Tool $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    public function run(): void
    {
        $transport = new StdioTransport();

        while (true) {
            $message = $transport->receive();

            if ($message === null) {
                break;
            }

            $response = $this->handleMessage($message);

            if ($response !== null) {
                $transport->send($response);
            }
        }
    }

    protected function handleMessage(array $message): ?array
    {
        $method = $message['method'] ?? null;
        $id     = $message['id'] ?? null;
        $params = $message['params'] ?? [];

        // Notifications never get a response
        if ($id === null) {
            return null;
        }

        return match ($method) {
            'initialize'  => $this->handleInitialize($id),
            'ping'        => $this->success($id, new \stdClass()),
            'tools/list'  => $this->handleToolsList($id),
            'tools/call'  => $this->handleToolCall($id, $params),
            default       => $this->error($id, -32601, "Method not found: {$method}"),
        };
    }

    protected function handleInitialize(mixed $id): array
    {
        return $this->success($id, [
            'protocolVersion' => '2024-11-05',
            'capabilities'    => ['tools' => new \stdClass()],
            'serverInfo'      => ['name' => 'laravel-mcp-server', 'version' => '1.0.0'],
        ]);
    }

    protected function handleToolsList(mixed $id): array
    {
        $tools = array_map(fn(Tool $tool) => [
            'name'        => $tool->getName(),
            'description' => $tool->getDescription(),
            'inputSchema' => $tool->getInputSchema(),
        ], array_values($this->tools));

        return $this->success($id, ['tools' => $tools]);
    }

    protected function handleToolCall(mixed $id, array $params): array
    {
        $name      = $params['name'] ?? null;
        $arguments = $params['arguments'] ?? [];

        if (!isset($this->tools[$name])) {
            return $this->error($id, -32602, "Tool not found: {$name}");
        }

        try {
            $result = $this->tools[$name]->execute($arguments);

            return $this->success($id, [
                'content' => [['type' => 'text', 'text' => $result]],
            ]);
        } catch (\Throwable $e) {
            return $this->success($id, [
                'content' => [['type' => 'text', 'text' => 'Error: ' . $e->getMessage()]],
                'isError' => true,
            ]);
        }
    }

    protected function success(mixed $id, mixed $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    protected function error(mixed $id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }
}
