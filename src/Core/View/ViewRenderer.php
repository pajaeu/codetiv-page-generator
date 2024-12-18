<?php

namespace Core\View;

interface ViewRenderer
{

    public function render(string $template, array $data = []): string;

	public function getTemplatePath(string $template): string;
}