<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

abstract class Tool
{
    abstract public function getName(): string;

    abstract public function getDescription(): string;

    abstract public function getInputSchema(): array;

    abstract public function execute(array $arguments): string;
}
