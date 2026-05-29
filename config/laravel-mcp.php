<?php

return [
    /*
     * Tools to register with the MCP server.
     * Comment out any tools you don't want to expose.
     */
    'tools' => [
        \Sourcecodeguy1\LaravelMcp\Tools\ListRoutesTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\GetSchemaTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\ListModelsTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\GetMigrationsTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\GetEnvKeysTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\ListMiddlewareTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\GetLogsTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\ListEventsTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\ListCommandsTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\GetConfigTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\ListJobsTool::class,
    ],
];
