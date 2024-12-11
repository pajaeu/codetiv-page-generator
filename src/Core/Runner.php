<?php

namespace Core;

use Core\Config\SiteConfig;
use Core\Data\Exceptions\InvalidDataProviderException;
use Core\Data\Provider;
use Core\Http\Page;
use Core\Http\RouteLoader;
use Core\Http\RouteRunner;
use Psr\Container\ContainerInterface;

class Runner
{

	public static Runner $instance;
	private SiteConfig $config;

	public function __construct(
		public string $basePath,
		private readonly ContainerInterface $container
	)
	{
		self::$instance = $this;

		$this->config = $this->container->get(SiteConfig::class);
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
		$directory = $this->basePath . $this->config->getSiteDir();

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

				$file = $this->basePath . $this->config->getSiteDir() . $fileName;

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

			$assetsDirectory = $this->basePath . $this->config->getSiteDir() . '/' . $this->config->getAssetsDir();

			if (!is_dir($assetsDirectory)) {
				mkdir($assetsDirectory, recursive: true);
			}

			$resourcesDirectory = $this->basePath . $this->config->getResourcesDir();

			$this->_copyDirectory($resourcesDirectory, $assetsDirectory);
		}
	}

	private function help(): void
	{
		echo "\e[0;30;43mUsage: php generate [command]\e[0m" . PHP_EOL;
		echo "Available commands:" . PHP_EOL;
		echo "  \e[0;32mbuild\e[0m - Build the site" . PHP_EOL;
		echo "  \e[0;31mcleanup\e[0m - Cleanup the site" . PHP_EOL;
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

	private function copyDirectory(string $source, string $destination): void
	{
		if (!is_dir($destination)) {
			mkdir($destination, recursive: true);
		}

		$this->_copyDirectory($source, $destination);
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

	/**
	 * @param string $source
	 * @param string $destination
	 * @return void
	 */
	private function _copyDirectory(string $source, string $destination): void
	{
		$files = array_diff(scandir($source), ['.', '..']);

		foreach ($files as $file) {
			$sourcePath = $source . '/' . $file;
			$destinationPath = $destination . '/' . $file;

			if (is_dir($sourcePath)) {
				$this->copyDirectory($sourcePath, $destinationPath);
			} else {
				copy($sourcePath, $destinationPath);
			}
		}
	}
}