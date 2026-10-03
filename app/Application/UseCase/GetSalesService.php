<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\PagedResult;
use App\Application\DTO\SaleView;
use App\Application\Model\DateRange;
use App\Application\Model\PageRequest;
use App\Application\Ports\Inbound\GetSales;
use App\Application\Ports\Outbound\SaleRepository;
use App\Domain\Exception\SaleNotFoundException;

final readonly class GetSalesService implements GetSales
{
    public function __construct(
        private SaleRepository $saleRepository,
    ) {
    }

    public function search(DateRange $range, PageRequest $pageRequest): PagedResult
    {
        return $this->saleRepository->search($range, $pageRequest);
    }

    public function getById(string $id): SaleView
    {
        $sale = $this->saleRepository->findById($id);
        if ($sale === null) {
            throw new SaleNotFoundException("La venta {$id} no existe.");
        }

        return $sale;
    }
}
