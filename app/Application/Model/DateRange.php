<?php

declare(strict_types=1);

namespace App\Application\Model;

use App\Domain\Exception\BusinessRuleViolation;
use DateTimeImmutable;

final readonly class DateRange
{
    private DateTimeImmutable $from;
    private DateTimeImmutable $to;

    public function __construct(DateTimeImmutable $from, DateTimeImmutable $to)
    {
        if ($to < $from) {
            throw new class('La fecha final no puede ser anterior a la inicial.') extends BusinessRuleViolation {};
        }

        $this->from = $from;
        $this->to = $to;
    }

    public static function of(DateTimeImmutable $from, DateTimeImmutable $to): self
    {
        return new self($from, $to);
    }

    public function from(): DateTimeImmutable
    {
        return $this->from;
    }

    public function to(): DateTimeImmutable
    {
        return $this->to;
    }

    public function contains(DateTimeImmutable $instant): bool
    {
        // from inclusive, to exclusive: from <= instant < to
        return $this->from <= $instant && $instant < $this->to;
    }
}
