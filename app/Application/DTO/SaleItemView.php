<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class SaleItemView
{
    public function __construct(
        public string $productId,
        public string $productName,
        public int $quantity,
        public float $unitPrice,
        public float $subtotal
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'productId' => $this->productId,
            'productName' => $this->productName,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'subtotal' => $this->subtotal,
        ];
    }
}
