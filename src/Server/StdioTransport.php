<?php

namespace Sourcecodeguy1\LaravelMcp\Server;

class StdioTransport
{
    public function receive(): ?array
    {
        $line = fgets(STDIN);

        if ($line === false) {
            return null;
        }

        $line = trim($line);

        if (empty($line)) {
            return $this->receive();
        }

        $decoded = json_decode($line, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    public function send(array $message): void
    {
        $json = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        fwrite(STDOUT, $json . "\n");
        fflush(STDOUT);
    }
}
