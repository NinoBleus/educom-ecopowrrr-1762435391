<?php

namespace App\Infrastructure\Exception;

class AddressLookupException extends \RuntimeException
{
    public function __construct(string $message, int $statusCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->getCode();
    }
}
