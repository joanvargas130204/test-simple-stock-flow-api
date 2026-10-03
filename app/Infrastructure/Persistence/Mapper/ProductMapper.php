<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapper;

use App\Application\DTO\ProductView;
use App\Domain\Model\Product;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Persistence\Model\ProductModel;

final class ProductMapper
{
    public static function toDomain(ProductModel $model): Product
    {
        return new Product(
            id: $model->id,
            name: $model->name,
            price: Money::of((float) $model->price),
            stock: (int) $model->stock,
            categoryId: $model->category_id,
            imageKey: $model->image_key
        );
    }

    public static function toModel(Product $domain, ?ProductModel $existing = null): ProductModel
    {
        $model = $existing ?? new ProductModel();
        $model->id = $domain->id;
        $model->name = $domain->name;
        $model->price = $domain->price->getAmountAsFloat();
        $model->stock = $domain->stock;
        $model->category_id = $domain->categoryId;
        $model->image_key = $domain->imageKey;

        if ($existing === null) {
            $model->version = 1;
            $model->deleted_at = null;
        }

        return $model;
    }

    public static function toView(ProductModel $model, ?string $imageUrl = null): ProductView
    {
        $categoryName = $model->category?->name ?? 'General';

        return new ProductView(
            id: $model->id,
            name: $model->name,
            price: (float) $model->price,
            currency: 'COP',
            stock: (int) $model->stock,
            categoryId: $model->category_id,
            categoryName: $categoryName,
            imageUrl: $imageUrl ?? ($model->image_key !== null ? "/media/{$model->image_key}" : null),
        );
    }
}
