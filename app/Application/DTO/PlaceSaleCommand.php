<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class PlaceSaleCommand
{
    /**
     * @param PlaceSaleItemCommand[] $lines
     */
    public function __construct(
        public array $lines,
        public string $soldByUserId,
        public string $soldByUsername,
    ) {
    }
}
