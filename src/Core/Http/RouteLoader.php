<?php

namespace Core\Http;

use Core\Runner;

class RouteLoader
{

    public function load(): array
    {
        $routes = [];

        $basePath = Runner::$instance->basePath;

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath  . 'app'));

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace([$basePath, '.php', '/'], ['', '', '\\'], $file->getPathname());
            $className = ucfirst($relativePath);

            if (!class_exists($className)) {
                continue;
            }

            $class = new \ReflectionClass($className);

            if (!$class->isInstantiable()) {
                continue;
            }

            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $attributes = $method->getAttributes(Page::class);

                if (empty($attributes)) {
                    continue;
                }

                $attribute = reset($attributes);

                $route = $attribute->newInstance();

                $route->setHandler([$className, $method->getName()]);

                $routes[] = $route;
            }
        }

        return $routes;
    }
}