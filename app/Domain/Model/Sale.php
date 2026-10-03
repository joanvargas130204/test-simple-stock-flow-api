<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\EmptySaleException;
use App\Domain\Exception\RepeatedProductException;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Quantity;
use DateTimeImmutable;

final class Sale
{
    private string $id;
    private DateTimeImmutable $soldAt;
    private string $soldByUsername;
    private string $soldByUserId;
    /** @var array<string, SaleItem> */
    private array $items = [];

    /**
     * @param array<SaleItem> $items
     */
    public function __construct(
        string $id,
        DateTimeImmutable $soldAt,
        string $soldByUsername,
        string $soldByUserId,
        array $items = []
    ) {
        $this->id = $id;
        $this->soldAt = $soldAt;
        $this->soldByUsername = $soldByUsername;
        $this->soldByUserId = $soldByUserId;

        foreach ($items as $item) {
            $this->items[$item->productId()] = $item;
        }
    }

    public static function open(
        string $id,
        DateTimeImmutable $soldAt,
        string $soldByUsername,
        string $soldByUserId
    ): self {
        return new self($id, $soldAt, $soldByUsername, $soldByUserId);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function soldAt(): DateTimeImmutable
    {
        return $this->soldAt;
    }

    public function soldByUsername(): string
    {
        return $this->soldByUsername;
    }

    public function soldByUserId(): string
    {
        return $this->soldByUserId;
    }

    /**
     * @return array<SaleItem>
     */
    public function items(): array
    {
        return array_values($this->items);
    }

    public function addItem(
        string $itemId,
        Product $product,
        string $categoryName,
        Quantity $quantity
    ): SaleItem {
        $productId = $product->id();
        if (isset($this->items[$productId])) {
            throw new RepeatedProductException();
        }

        // Withdraw stock from product
        $product->withdraw($quantity);

        // Freeze product name, category name and unit price
        $item = new SaleItem(
            $itemId,
            $productId,
            $product->name(),
            $categoryName,
            $quantity,
            $product->price()
        );

        $this->items[$productId] = $item;

        return $item;
    }

    public function total(): Money
    {
        $total = Money::zero();
        foreach ($this->items as $item) {
            $total = $total->plus($item->subtotal());
        }
        return $total;
    }

    public function ensureConfirmable(): void
    {
        if (empty($this->items)) {
            throw new EmptySaleException();
        }
    }
}
