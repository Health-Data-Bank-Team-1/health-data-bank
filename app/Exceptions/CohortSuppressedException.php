<?php

namespace App\Exceptions;

use Exception;

class CohortSuppressedException extends Exception
{
    public function __construct(
        string $message = 'Cohort suppressed because size is below minimum threshold.'
    ) {
        parent::__construct($message);
    }
}
