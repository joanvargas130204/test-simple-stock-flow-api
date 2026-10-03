<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\Exception\InsufficientStockException;
use App\Domain\Exception\InvalidPriceException;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Quantity;

final class Product
{
    private string $id;
    private string $name;
    private Money $price;
    private int $stock;
    private string $categoryId;
    private ?string $imageKey;

    public function __construct(
        string $id,
        string $name,
        Money $price,
        int $stock,
        string $categoryId,
        ?string $imageKey = null
    ) {
        $trimmedName = trim($name);
        if ($trimmedName === '') {
            throw new class('El nombre del producto es obligatorio.') extends BusinessRuleViolation {};
        }

        if (!$price->isPositive()) {
            throw new InvalidPriceException('El precio debe ser mayor a cero.');
        }

        if ($stock < 0) {
            throw new class('El stock inicial no puede ser negativo.') extends BusinessRuleViolation {};
        }

        $trimmedCategory = trim($categoryId);
        if ($trimmedCategory === '' || $trimmedCategory === '00000000-0000-0000-0000-000000000000') {
            throw new class('La categoría es obligatoria.') extends BusinessRuleViolation {};
        }

        $this->id = $id;
        $this->name = $trimmedName;
        $this->price = $price;
        $this->stock = $stock;
        $this->categoryId = $trimmedCategory;
        $this->imageKey = ($imageKey !== null && trim($imageKey) !== '') ? trim($imageKey) : null;
    }

    public static function create(
        string $id,
        string $name,
        Money $price,
        int $stock,
        string $categoryId,
        ?string $imageKey = null
    ): self {
        return new self($id, $name, $price, $stock, $categoryId, $imageKey);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function imageKey(): ?string
    {
        return $this->imageKey;
    }

    public function withdraw(Quantity $quantity): void
    {
        $qty = $quantity->value();
        if ($qty > $this->stock) {
            throw InsufficientStockException::forProduct($this->name, $this->stock, $qty);
        }

        $this->stock -= $qty;
    }

    public function restock(Quantity $quantity): void
    {
        $this->stock += $quantity->value();
    }

    public function changePrice(Money $newPrice): void
    {
        if (!$newPrice->isPositive()) {
            throw new InvalidPriceException('El precio debe ser mayor a cero.');
        }

        $this->price = $newPrice;
    }

    public function rename(string $newName): void
    {
        $trimmed = trim($newName);
        if ($trimmed === '') {
            throw new class('El nombre del producto es obligatorio.') extends BusinessRuleViolation {};
        }
        $this->name = $trimmed;
    }

    public function setCategory(string $categoryId): void
    {
        $trimmed = trim($categoryId);
        if ($trimmed === '' || $trimmed === '00000000-0000-0000-0000-000000000000') {
            throw new class('La categoría es obligatoria.') extends BusinessRuleViolation {};
        }
        $this->categoryId = $trimmed;
    }

    public function attachImage(?string $imageKey): void
    {
        $this->imageKey = ($imageKey !== null && trim($imageKey) !== '') ? trim($imageKey) : null;
    }
}
