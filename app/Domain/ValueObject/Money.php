<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidPriceException;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use InvalidArgumentException;

final readonly class Money
{
    public const DEFAULT_CURRENCY = 'COP';

    private BigDecimal $amount;
    private string $currency;

    private function __construct(BigDecimal $amount, string $currency = self::DEFAULT_CURRENCY)
    {
        if ($currency !== self::DEFAULT_CURRENCY) {
            throw new InvalidPriceException("La moneda '{$currency}' no es válida. El sistema opera únicamente en " . self::DEFAULT_CURRENCY . ".");
        }

        if ($amount->isNegative()) {
            throw new InvalidPriceException("El importe monetario no puede ser negativo.");
        }

        $this->amount = $amount->toScale(2, RoundingMode::HALF_UP);
        $this->currency = $currency;
    }

    public static function of(int|float|string|BigDecimal $amount, string $currency = self::DEFAULT_CURRENCY): self
    {
        $decimal = $amount instanceof BigDecimal ? $amount : BigDecimal::of((string) $amount);
        return new self($decimal, $currency);
    }

    public static function zero(string $currency = self::DEFAULT_CURRENCY): self
    {
        return new self(BigDecimal::zero(), $currency);
    }

    public function plus(self $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount->plus($other->amount), $this->currency);
    }

    public function multiply(int|Quantity $multiplier): self
    {
        $factor = $multiplier instanceof Quantity ? $multiplier->value() : $multiplier;
        return new self($this->amount->multipliedBy($factor), $this->currency);
    }

    public function amount(): BigDecimal
    {
        return $this->amount;
    }

    public function toFloat(): float
    {
        return $this->amount->toFloat();
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function isZero(): bool
    {
        return $this->amount->isZero();
    }

    public function isPositive(): bool
    {
        return $this->amount->isPositive();
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency && $this->amount->isEqualTo($other->amount);
    }

    private function ensureSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidPriceException("No se pueden operar importes con monedas distintas: {$this->currency} y {$other->currency}.");
        }
    }
}
