<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

use Illuminate\Support\Facades\Schema;

class GetSchemaTool extends Tool
{
    public function getName(): string
    {
        return 'get_schema';
    }

    public function getDescription(): string
    {
        return 'Get database schema information. List all tables, or pass a table name to get its columns and indexes.';
    }

    public function getInputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'table' => [
                    'type'        => 'string',
                    'description' => 'Table name to inspect. Leave empty to list all tables.',
                ],
            ],
        ];
    }

    public function execute(array $arguments): string
    {
        if (!empty($arguments['table'])) {
            $table = $arguments['table'];

            return json_encode([
                'table'   => $table,
                'columns' => Schema::getColumns($table),
                'indexes' => Schema::getIndexes($table),
            ], JSON_PRETTY_PRINT);
        }

        return json_encode([
            'tables' => Schema::getTables(),
        ], JSON_PRETTY_PRINT);
    }
}
