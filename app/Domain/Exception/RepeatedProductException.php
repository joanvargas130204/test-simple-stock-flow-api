<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class RepeatedProductException extends BusinessRuleViolation
{
    public function __construct(string $message = 'La venta tiene productos repetidos.')
    {
        parent::__construct($message);
    }
}
