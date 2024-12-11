<?php

namespace Core\Config;

readonly class SiteConfig
{

	public function __construct(
		private string $site_dir = 'public',
		private string $assets_dir  = 'assets',
		private string $resources_dir = 'resources',
	)
	{
	}

	public function getSiteDir(): string
	{
		return $this->site_dir;
	}

	public function getAssetsDir(): string
	{
		return $this->assets_dir;
	}

	public function getResourcesDir(): string
	{
		return $this->resources_dir;
	}
}