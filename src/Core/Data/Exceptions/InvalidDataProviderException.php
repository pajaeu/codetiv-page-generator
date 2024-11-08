<?php

namespace Core\Data\Exceptions;

class InvalidDataProviderException extends \Exception
{

    public function __construct($message = 'Invalid data provider', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}