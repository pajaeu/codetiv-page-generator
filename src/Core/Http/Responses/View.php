<?php

namespace Core\Http\Responses;

use Core\Http\Response;
use Core\Runner;

final class View extends Response
{

    public function __construct(
        string $template,
        ...$data
    )
    {
        $this->body = $this->render($template, $data);
    }

    private function render(string $template, array $data): false|string
    {
        $renderer = Runner::get(\Core\View\ViewRenderer::class);

        return $renderer->render($template, $data);
    }
}