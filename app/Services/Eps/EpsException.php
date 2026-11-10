<?php

namespace App\Services\Eps;

use RuntimeException;

class EpsException extends RuntimeException
{
    /** @param  array<string, mixed>  $response  decoded EPS response, if any */
    public function __construct(string $message, public readonly array $response = [])
    {
        parent::__construct($message);
    }
}
