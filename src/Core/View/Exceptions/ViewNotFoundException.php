<?php

namespace Core\View\Exceptions;

use Exception;

class ViewNotFoundException extends Exception
{

    public function __construct(string $path)
    {
        parent::__construct("View not found at path: {$path}");
    }
}