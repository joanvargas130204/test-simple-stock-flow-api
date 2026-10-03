<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\SalesReport;
use App\Application\Model\DateRange;
use App\Application\Ports\Inbound\GetSalesReport;
use App\Application\Ports\Outbound\SalesReportQuery;

final readonly class SalesReportService implements GetSalesReport
{
    public function __construct(
        private SalesReportQuery $salesReportQuery,
    ) {
    }

    public function execute(DateRange $range): SalesReport
    {
        return $this->salesReportQuery->execute($range);
    }
}
