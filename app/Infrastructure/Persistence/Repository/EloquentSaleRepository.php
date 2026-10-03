<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Application\DTO\PagedResult;
use App\Application\DTO\SaleView;
use App\Application\Model\DateRange;
use App\Application\Model\PageRequest;
use App\Application\Ports\Outbound\SaleRepository;
use App\Domain\Model\Sale;
use App\Infrastructure\Persistence\Mapper\SaleMapper;
use App\Infrastructure\Persistence\Model\SaleItemModel;
use App\Infrastructure\Persistence\Model\SaleModel;

final class EloquentSaleRepository implements SaleRepository
{
    public function save(Sale $sale): void
    {
        $saleModel = SaleMapper::toModel($sale);
        $saleModel->save();

        foreach ($sale->items as $item) {
            $itemModel = SaleMapper::toItemModel($item, $sale->id);
            $itemModel->save();
        }
    }

    public function findById(string $id): ?SaleView
    {
        $model = SaleModel::with('items')->find($id);

        return $model !== null ? SaleMapper::toView($model) : null;
    }

    public function search(DateRange $range, PageRequest $pageRequest): PagedResult
    {
        $fromStr = $range->from->format('Y-m-d H:i:s.u');
        $toStr = $range->to->format('Y-m-d H:i:s.u');

        $builder = SaleModel::with('items')
            ->where('sold_at', '>=', $fromStr)
            ->where('sold_at', '<', $toStr);

        $total = $builder->count();

        $models = $builder->orderBy('sold_at', 'desc')
            ->skip(($pageRequest->page - 1) * $pageRequest->size)
            ->take($pageRequest->size)
            ->get();

        $items = $models->map(fn (SaleModel $m) => SaleMapper::toView($m))->all();

        return new PagedResult(
            items: $items,
            page: $pageRequest->page,
            size: $pageRequest->size,
            total: $total
        );
    }
}
