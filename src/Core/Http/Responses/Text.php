<?php

namespace Core\Http\Responses;

use Core\Http\Response;

class Text extends Response
{

    public function __construct(
        string $content
    )
    {
        $this->body = $content;
    }
}