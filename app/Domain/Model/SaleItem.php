<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Quantity;

final class SaleItem
{
    private string $id;
    private string $productId;
    private string $productName;
    private string $categoryName;
    private Quantity $quantity;
    private Money $unitPrice;

    public function __construct(
        string $id,
        string $productId,
        string $productName,
        string $categoryName,
        Quantity $quantity,
        Money $unitPrice
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->productName = $productName;
        $this->categoryName = $categoryName;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function categoryName(): string
    {
        return $this->categoryName;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function subtotal(): Money
    {
        return $this->unitPrice->multiply($this->quantity);
    }
}
