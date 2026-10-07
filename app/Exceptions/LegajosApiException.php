<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class LegajosApiException extends RuntimeException
{
    private ?int $status;

    public function __construct(string $message, ?int $status = null, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->status = $status;
    }

    public function status(): ?int
    {
        return $this->status;
    }
}
