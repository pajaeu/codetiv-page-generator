<?php

namespace Core\Http;

use Core\Http\Exceptions\HttpRouteNotFoundException;
use Psr\Container\ContainerInterface;

final class RouteRunner
{

    private array $routes = [];

    public function __construct(
        private readonly ContainerInterface $container,
    )
    {
    }

    public function setRoutes(array $routes): void
    {
        $this->routes = $routes;
    }

    public function dispatch(string $path): Response
    {
        $route = $this->match($path);

        if ($route === null) {
            throw new HttpRouteNotFoundException();
        }

        $callable = $this->getCallable($route);

        return $callable(...array_values($route->getParams()));
    }

    private function match(string $path): ?Matched
    {
        /** @var Page $route */
        foreach ($this->routes as $route) {
            $regex = $this->convertPathToRegex($route->getPath());

            $matching = preg_match($regex, $path, $params);

            $routeParams = $this->resolveParams($params);

            if ($matching) {
                return new Matched(
                    path: $route->getPath(),
                    handler: $route->getHandler(),
                    params: $routeParams
                );
            }
        }

        return null;
    }

    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);

        return '#^' . $pattern . '$#';
    }

    private function getCallable(Matched $route): array|string|\Closure|null
    {
        $callable = $route->getHandler();

        if (is_array($callable)) {
            [$controller, $method] = $callable;

            $controller = $this->container->get($controller);

            $callable = fn(...$params) => $controller->$method(...$params);
        }

        return $callable;
    }

    private function resolveParams(array $params): array
    {
        $resolved = [];

        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }
}