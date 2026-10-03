<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class SalesReport
{
    /**
     * @param SalesReportRow[] $rows
     */
    public function __construct(
        public string $from,
        public string $to,
        public int $salesCount,
        public float $grandTotal,
        public string $currency,
        public array $rows,
    ) {
    }

    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'salesCount' => $this->salesCount,
            'grandTotal' => $this->grandTotal,
            'currency' => $this->currency,
            'rows' => array_map(fn (SalesReportRow $row) => $row->toArray(), $this->rows),
        ];
    }
}
