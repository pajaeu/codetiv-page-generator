<?php

namespace App;

use Core\Http\Responses\View;

class Base
{

    protected function view(string $template, ...$data): View
    {
        return new View($template, ...$data);
    }
}