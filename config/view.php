<?php

return new \Core\View\Renderers\LatteRenderer(
    viewPath: __DIR__ . '/../templates',
    latte: new \Latte\Engine(),
);