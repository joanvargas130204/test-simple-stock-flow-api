<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Application\DTO\PagedResult;
use App\Application\DTO\SaleView;
use App\Application\Model\DateRange;
use App\Application\Model\PageRequest;
use App\Domain\Model\Sale;

interface SaleRepository
{
    public function save(Sale $sale): void;

    public function findById(string $id): ?SaleView;

    /**
     * @return PagedResult<SaleView>
     */
    public function search(DateRange $range, PageRequest $pageRequest): PagedResult;
}
