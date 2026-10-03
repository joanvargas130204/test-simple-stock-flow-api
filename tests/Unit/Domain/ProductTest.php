<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\Exception\InsufficientStockException;
use App\Domain\Exception\InvalidPriceException;
use App\Domain\Model\Product;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function testCanCreateProductAndWithdrawStock(): void
    {
        $product = new Product(
            id: '11111111-1111-4111-8111-111111111111',
            name: ' Martillo de Acero ',
            price: Money::of(45000.0),
            stock: 10,
            categoryId: '22222222-2222-4222-8222-222222222222'
        );

        $this->assertEquals('Martillo de Acero', $product->name);
        $this->assertEquals(10, $product->stock);

        $product->withdraw(new Quantity(4));
        $this->assertEquals(6, $product->stock);
    }

    public function testCannotWithdrawMoreThanAvailableStock(): void
    {
        $product = new Product(
            id: '11111111-1111-4111-8111-111111111111',
            name: 'Tornillo',
            price: Money::of(500.0),
            stock: 2,
            categoryId: '22222222-2222-4222-8222-222222222222'
        );

        $this->expectException(InsufficientStockException::class);
        $product->withdraw(new Quantity(3));
    }
}
