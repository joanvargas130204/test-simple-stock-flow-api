<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class ProductView
{
    public function __construct(
        public string $id,
        public string $name,
        public float $price,
        public string $currency,
        public int $stock,
        public string $categoryId,
        public string $categoryName,
        public ?string $imageUrl
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'currency' => $this->currency,
            'stock' => $this->stock,
            'categoryId' => $this->categoryId,
            'categoryName' => $this->categoryName,
            'imageUrl' => $this->imageUrl,
        ];
    }
}
