<?php

namespace Core\Http;

class Response
{

    protected string|array|null $body = null;

    public function getBody(): string|array|null
    {
        return $this->body;
    }
}