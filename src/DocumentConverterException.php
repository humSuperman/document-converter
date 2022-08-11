<?php

namespace DocumentConverter;

use Throwable;

class DocumentConverterException extends \RuntimeException
{
    public function __construct($message = '', $code = 10000, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}