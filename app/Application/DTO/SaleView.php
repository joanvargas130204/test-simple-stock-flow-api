<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class SaleView
{
    /**
     * @param array<SaleItemView> $items
     */
    public function __construct(
        public string $id,
        public string $soldAt,
        public string $soldBy,
        public float $total,
        public string $currency,
        public array $items
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'soldAt' => $this->soldAt,
            'soldBy' => $this->soldBy,
            'total' => $this->total,
            'currency' => $this->currency,
            'items' => array_map(fn(SaleItemView $item) => $item->toArray(), $this->items),
        ];
    }
}
