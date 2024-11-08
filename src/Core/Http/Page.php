<?php

namespace Core\Http;

use Closure;
use Core\Data\DataProvider;
use Core\Data\Provider;

#[\Attribute]
class Page
{

    private Closure|string|array|null $handler = null;

    public function __construct(
        private readonly string $path = '/',
        private readonly string|Provider $provider = DataProvider::class
    )
    {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getProvider(): string|Provider
    {
        return $this->provider;
    }

    public function getHandler(): array|string|Closure|null
    {
        return $this->handler;
    }

    public function setHandler(array|string|Closure $handler): void
    {
        $this->handler = $handler;
    }
}