<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class SalesReportRow
{
    public function __construct(
        public string $productId,
        public string $productName,
        public string $categoryName,
        public int $unitsSold,
        public float $revenue,
    ) {
    }

    public function toArray(): array
    {
        return [
            'productId' => $this->productId,
            'productName' => $this->productName,
            'categoryName' => $this->categoryName,
            'unitsSold' => $this->unitsSold,
            'revenue' => $this->revenue,
        ];
    }
}
