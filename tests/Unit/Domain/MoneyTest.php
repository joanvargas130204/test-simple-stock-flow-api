<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Exception\InvalidPriceException;
use App\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testCanCreateValidMoney(): void
    {
        $money = Money::of(12500.50);
        $this->assertEquals(12500.50, $money->getAmountAsFloat());
        $this->assertEquals('COP', $money->currency);
    }

    public function testRoundsWithHalfUpToTwoDecimals(): void
    {
        $money = Money::of(10.555);
        $this->assertEquals(10.56, $money->getAmountAsFloat());
    }

    public function testRejectsNegativeAmount(): void
    {
        $this->expectException(InvalidPriceException::class);
        Money::of(-50.0);
    }
}
