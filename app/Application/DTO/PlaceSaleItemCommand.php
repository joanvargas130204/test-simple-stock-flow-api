<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class PlaceSaleItemCommand
{
    public function __construct(
        public string $productId,
        public int $quantity,
    ) {
    }
}
