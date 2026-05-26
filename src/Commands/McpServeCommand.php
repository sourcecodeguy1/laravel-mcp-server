<?php

namespace Sourcecodeguy1\LaravelMcp\Commands;

use Illuminate\Console\Command;
use Sourcecodeguy1\LaravelMcp\Server\McpServer;
use Sourcecodeguy1\LaravelMcp\Tools\Tool;

class McpServeCommand extends Command
{
    protected $signature   = 'mcp:serve';
    protected $description = 'Start the MCP server for AI agent integration (Claude Desktop, etc.)';

    public function handle(): void
    {
        $server = new McpServer();

        foreach (config('laravel-mcp.tools', []) as $toolClass) {
            /** @var Tool $tool */
            $tool = app($toolClass);
            $server->registerTool($tool);
        }

        $server->run();
    }
}
