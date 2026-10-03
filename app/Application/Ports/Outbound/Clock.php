<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
