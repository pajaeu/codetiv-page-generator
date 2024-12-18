<?php

namespace Core;

use Core\Config\SiteConfig;
use Core\Data\Exceptions\InvalidDataProviderException;
use Core\Data\Provider;
use Core\Http\Page;
use Core\Http\RouteLoader;
use Core\Http\RouteRunner;
use Core\View\ViewRenderer;
use Psr\Container\ContainerInterface;

class Runner
{

	public static Runner $instance;

	public function __construct(
		public string $basePath,
		private readonly ContainerInterface $container
	)
	{
		self::$instance = $this;
	}

	public static function get(?string $key = null)
	{
		if ($key) {
			return self::$instance->container->get($key);
		}

		return self::$instance;
	}

	public static function boot(
		string $basePath,
		ContainerInterface $container
	): Runner
	{
		return new Runner($basePath, $container);
	}

	public function run(array $arguments): void
	{
		$command = $this->getCommand($arguments);
		$arguments = $this->parseArguments($arguments);

		match ($command) {
			'build' => $this->build(),
			'cleanup' => $this->cleanup($arguments),
			default => $this->help(),
		};
	}

	private function cleanup(array $args = []): void
	{
		$directory = $this->basePath . 'public';

		if (in_array('content', $args)) {
			echo "\e[0;30;43m ✓ Including content directory \e[0m" . PHP_EOL;

			$contentDirectory = $this->basePath . '/_content';

			$this->deleteDirectory($contentDirectory);
		}

		$this->deleteDirectory($directory);

		echo "\e[0;30;42m ✓ Cleanup completed \e[0m" . PHP_EOL;
	}

	private function build(): void
	{
		$this->generateHtaccess();

		$this->generateErrorPages();

		$this->generateSite();
	}

	private function help(): void
	{
		echo "\e[0;30;43mUsage: php generate [command]\e[0m" . PHP_EOL;
		echo "Available commands:" . PHP_EOL;
		echo "  \e[0;32mbuild\e[0m - Build the site" . PHP_EOL;
		echo "  \e[0;31mcleanup\e[0m - Cleanup the site" . PHP_EOL;
	}

	private function generateHtaccess(): void
	{
		$file = $this->basePath . 'public/.htaccess';

		$content = <<<HTACCESS
        ErrorDocument 404 /404.html
        ErrorDocument 500 /500.html
        HTACCESS;

		file_put_contents($file, $content);
	}

	private function generateErrorPages(): void
	{
		$errorPages = [
			'404' => 'Not Found',
			'500' => 'Internal Server Error',
		];

		foreach ($errorPages as $code => $message) {
			$content = <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>$code $message</title>
            </head>
            <body>
                <h1>$code $message</h1>
            </body>
            </html>
            HTML;

			/** @var ViewRenderer $renderer */
			$renderer = $this->container->get(ViewRenderer::class);

			$template = $renderer->getTemplatePath($code);

			if (file_exists($template)) {
				$content = $renderer->render($code);
			}

			$file = $this->basePath . 'public' . $code . '.html';

			file_put_contents($file, $content);
		}
	}

	private function generateSite(): void
	{
		$routes = (new RouteLoader())->load();

		/** @var RouteRunner $routeRunner */
		$routeRunner = $this->container->get(RouteRunner::class);

		$routeRunner->setRoutes($routes);

		/** @var Page $route */
		foreach ($routes as $route) {
			$provider = $route->getProvider();

			if (is_string($route->getProvider())) {
				$provider = $this->container->get($provider);
			}

			if (!$provider instanceof Provider) {
				throw new InvalidDataProviderException();
			}

			foreach ($provider->provide() as $value) {
				$uri = $this->parseUri($route->getPath(), $value);

				$fileName = $uri === '/' ? '/index.html' : $uri. '/index.html';

				$file = $this->basePath . 'public' . $fileName;

				try {
					$response = $routeRunner->dispatch($uri);

					$body = $response->getBody();

					$directory = pathinfo($file, PATHINFO_DIRNAME);

					if (!is_dir($directory)) {
						mkdir($directory, recursive: true);
					}

					file_put_contents($file, $body);

					echo "\e[0;30;42m ✓ Generated \e[0m \e[1;32m$uri\e[0m" . PHP_EOL;
				} catch (\Throwable $e) {
					echo "\e[0;30;41m ✕ Error generating \e[0m $uri\e[0m \e[0;31m" . $e->getMessage() . "\e[0m" . PHP_EOL;
				}
			}
		}
	}

	private function deleteDirectory(string $directory): void
	{
		$files = array_diff(scandir($directory), ['.', '..']);

		foreach ($files as $file) {
			$path = $directory . '/' . $file;

			if (is_dir($path)) {
				$this->deleteDirectory($path);
			} else {
				unlink($path);
			}
		}

		rmdir($directory);
	}

	private function getCommand(array $arguments)
	{
		return $arguments[1] ?? null;
	}

	private function parseArguments(array $arguments): array
	{
		$fileName = array_shift($arguments);

		$parsedArguments = [];

		foreach ($arguments as $argument) {
			if (str_starts_with($argument, '--')) {
				$argument = ltrim($argument, '--');

				$parsedArguments[$argument] = true;
			}
		}

		return $parsedArguments;
	}

	private function parseUri(string $getPath, mixed $value): array|string
	{
		$uri = $getPath;

		foreach ($value as $key => $val) {
			$uri = str_replace('{' . $key . '}', $val, $uri);
		}

		return $uri;
	}
}