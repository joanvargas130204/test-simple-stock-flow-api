<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\DTO\PlaceSaleCommand;

interface PlaceSale
{
    public function execute(PlaceSaleCommand $command): string;
}
