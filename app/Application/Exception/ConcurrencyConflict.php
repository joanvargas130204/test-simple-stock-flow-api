<?php

declare(strict_types=1);

namespace App\Application\Exception;

use RuntimeException;

final class ConcurrencyConflict extends RuntimeException
{
    public function __construct(string $message = 'Otra operación modificó los datos al mismo tiempo. Inténtalo de nuevo.')
    {
        parent::__construct($message);
    }
}
