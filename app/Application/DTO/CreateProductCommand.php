<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class CreateProductCommand
{
    public function __construct(
        public string $name,
        public float $price,
        public int $stock,
        public string $categoryId,
    ) {
    }
}
