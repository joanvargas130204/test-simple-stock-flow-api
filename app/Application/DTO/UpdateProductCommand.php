<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class UpdateProductCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public float $price,
        public int $stock,
        public string $categoryId,
    ) {
    }
}
