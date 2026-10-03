<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\DTO\PagedResult;
use App\Application\DTO\SaleView;
use App\Application\Model\DateRange;
use App\Application\Model\PageRequest;

interface GetSales
{
    /**
     * @return PagedResult<SaleView>
     */
    public function search(DateRange $range, PageRequest $pageRequest): PagedResult;

    public function getById(string $id): SaleView;
}
