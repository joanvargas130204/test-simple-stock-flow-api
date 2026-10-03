<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapper;

use App\Application\DTO\SaleItemView;
use App\Application\DTO\SaleView;
use App\Domain\Model\Sale;
use App\Domain\Model\SaleItem;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Quantity;
use App\Infrastructure\Persistence\Model\SaleItemModel;
use App\Infrastructure\Persistence\Model\SaleModel;

final class SaleMapper
{
    public static function toDomain(SaleModel $model): Sale
    {
        $sale = new Sale(
            id: $model->id,
            soldAt: \DateTimeImmutable::createFromMutable($model->sold_at),
            soldByUsername: $model->sold_by_username,
            soldByUserId: $model->sold_by_user_id
        );

        foreach ($model->items as $itemModel) {
            $item = new SaleItem(
                id: $itemModel->id,
                productId: $itemModel->product_id,
                productName: $itemModel->product_name,
                categoryName: $itemModel->category_name,
                quantity: new Quantity((int) $itemModel->quantity),
                unitPrice: Money::of((float) $itemModel->unit_price)
            );
            $sale->loadItem($item);
        }

        return $sale;
    }

    public static function toModel(Sale $domain): SaleModel
    {
        $model = new SaleModel();
        $model->id = $domain->id;
        $model->sold_at = $domain->soldAt->format('Y-m-d H:i:s.u');
        $model->sold_by_username = $domain->soldByUsername;
        $model->sold_by_user_id = $domain->soldByUserId;

        return $model;
    }

    public static function toItemModel(SaleItem $item, string $saleId): SaleItemModel
    {
        $model = new SaleItemModel();
        $model->id = $item->id;
        $model->sale_id = $saleId;
        $model->product_id = $item->productId;
        $model->product_name = $item->productName;
        $model->category_name = $item->categoryName;
        $model->quantity = $item->quantity->value;
        $model->unit_price = $item->unitPrice->getAmountAsFloat();

        return $model;
    }

    public static function toView(SaleModel $model): SaleView
    {
        $itemViews = [];
        $total = 0.0;

        foreach ($model->items as $item) {
            $qty = (int) $item->quantity;
            $unitPrice = (float) $item->unit_price;
            $subtotal = round($qty * $unitPrice, 2);
            $total += $subtotal;

            $itemViews[] = new SaleItemView(
                productId: $item->product_id,
                productName: $item->product_name,
                quantity: $qty,
                unitPrice: $unitPrice,
                subtotal: $subtotal
            );
        }

        $soldAtIso = $model->sold_at instanceof \DateTimeInterface
            ? $model->sold_at->format('Y-m-d\TH:i:s.u\+00:00')
            : (new \DateTimeImmutable((string) $model->sold_at))->format('Y-m-d\TH:i:s.u\+00:00');

        return new SaleView(
            id: $model->id,
            soldAt: $soldAtIso,
            soldBy: $model->sold_by_username,
            total: round($total, 2),
            currency: 'COP',
            items: $itemViews
        );
    }
}
