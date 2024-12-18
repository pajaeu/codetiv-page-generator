<?php

namespace Core\View\Renderers;

use Core\View\Exceptions\ViewNotFoundException;
use Core\View\ViewRenderer;
use Latte\Engine;

final readonly class LatteRenderer implements ViewRenderer
{

    public function __construct(
        private ?string $viewPath,
        private Engine $latte
    )
    {
    }


    public function render(string $template, array $data = []): string
    {
        $path = $this->resolvePath($template);

        if (!file_exists($path)) {
            throw new ViewNotFoundException($path);
        }

        return $this->latte->renderToString($path, $data);
    }

	public function getTemplatePath(string $template): string
	{
		return $this->resolvePath($template);
	}

	private function resolvePath(string $template): string
	{
		return sprintf('%s/%s.latte', $this->viewPath, str_replace('.', '/', $template));
	}
}