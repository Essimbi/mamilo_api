<?php

namespace App\Exceptions;

use Exception;

class ResourceNotFoundException extends Exception
{
    protected $message = 'Resource not found';
    protected $code = 404;
}
