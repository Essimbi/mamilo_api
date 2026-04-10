<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $code = 422;

    public function __construct(string $message = 'Validation failed', array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors ?? [];
    }
}
