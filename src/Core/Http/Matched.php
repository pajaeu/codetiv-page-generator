<?php

namespace Core\Http;

use Closure;

final readonly class Matched
{

    public function __construct(
        private string $path = '/',
        private array $handler = [],
        private array $params = []
    )
    {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHandler(): array|string|Closure|null
    {
        return $this->handler;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}