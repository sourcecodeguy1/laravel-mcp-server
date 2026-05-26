# laravel-mcp-server

An MCP (Model Context Protocol) server for Laravel applications. Install it in any Laravel app, run one Artisan command, and AI agents like Claude get live read-only access to your application's internals — routes, database schema, Eloquent models, migrations, and environment keys.

## Requirements

- PHP 8.1+
- Laravel 10, 11, 12, or 13

## Installation

```bash
composer require sourcecodeguy1/laravel-mcp-server
```

The service provider is auto-discovered. Optionally publish the config:

```bash
php artisan vendor:publish --tag=laravel-mcp-config
```

## Usage

Start the MCP server:

```bash
php artisan mcp:serve
```

The server runs over stdio and speaks the [Model Context Protocol](https://modelcontextprotocol.io). Connect any MCP-compatible client to it.

## Claude Desktop Setup

Add this to your Claude Desktop config (`claude_desktop_config.json`):

```json
{
  "mcpServers": {
    "laravel": {
      "command": "php",
      "args": ["/absolute/path/to/your/laravel/app/artisan", "mcp:serve"]
    }
  }
}
```

Then restart Claude Desktop. You'll see a hammer icon in the chat — that means the tools are connected.

## Available Tools

| Tool | Description |
|---|---|
| `list_routes` | All registered routes with method, URI, controller, middleware. Filterable by HTTP method or URI pattern. |
| `get_schema` | List all database tables, or pass a table name to get columns and indexes. |
| `list_models` | All Eloquent models with table name, fillable/hidden fields, and detected relationships. |
| `get_migrations` | Migration status — which have been run (with batch number) and which are pending. |
| `get_env_keys` | All `.env` key names. **Never returns values** — keys only, for security. |

## Example Questions You Can Ask Claude

- *"What routes in this app are missing auth middleware?"*
- *"Show me the columns in the users table"*
- *"Which models have a BelongsToMany relationship?"*
- *"Are there any pending migrations?"*
- *"What third-party services does this app integrate with?"* (from env keys)

## Configuration

After publishing the config, you can enable/disable individual tools:

```php
// config/laravel-mcp.php
return [
    'tools' => [
        \Sourcecodeguy1\LaravelMcp\Tools\ListRoutesTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\GetSchemaTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\ListModelsTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\GetMigrationsTool::class,
        \Sourcecodeguy1\LaravelMcp\Tools\GetEnvKeysTool::class,
    ],
];
```

## Security

- **Read-only** — no tool writes to the database or filesystem
- **Env values never exposed** — `get_env_keys` returns key names only
- Intended for local development use with Claude Desktop, not production exposure

## License

MIT — [Julio Sandoval](https://juliowebmaster.com)
