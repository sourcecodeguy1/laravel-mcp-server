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
    ],
];
