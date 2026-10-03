<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Application\DTO\PagedResult;
use App\Application\DTO\ProductView;
use App\Application\Exception\ConcurrencyConflict;
use App\Application\Model\PageRequest;
use App\Application\Ports\Outbound\ProductRepository;
use App\Domain\Model\Product;
use App\Infrastructure\Persistence\Mapper\ProductMapper;
use App\Infrastructure\Persistence\Model\ProductModel;

final class EloquentProductRepository implements ProductRepository
{
    public function findById(string $id): ?Product
    {
        $model = ProductModel::find($id);

        return $model !== null ? ProductMapper::toDomain($model) : null;
    }

    public function findByIdActive(string $id): ?Product
    {
        $model = ProductModel::where('id', $id)->whereNull('deleted_at')->first();

        return $model !== null ? ProductMapper::toDomain($model) : null;
    }

    public function findManyActive(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $models = ProductModel::whereIn('id', $ids)->whereNull('deleted_at')->get();
        $result = [];
        foreach ($models as $model) {
            $result[$model->id] = ProductMapper::toDomain($model);
        }

        return $result;
    }

    public function search(?string $query, ?string $categoryId, PageRequest $pageRequest): PagedResult
    {
        $builder = ProductModel::with('category')->whereNull('deleted_at');

        if ($categoryId !== null) {
            $builder->where('category_id', $categoryId);
        }

        if ($query !== null && trim($query) !== '') {
            $escaped = addcslashes($query, '%_\\');
            $builder->where('name', 'LIKE', '%' . $escaped . '%');
        }

        $total = $builder->count();

        $models = $builder->orderBy('name', 'asc')
            ->skip(($pageRequest->page - 1) * $pageRequest->size)
            ->take($pageRequest->size)
            ->get();

        $items = $models->map(fn (ProductModel $m) => ProductMapper::toView($m))->all();

        return new PagedResult(
            items: $items,
            page: $pageRequest->page,
            size: $pageRequest->size,
            total: $total
        );
    }

    public function save(Product $product): void
    {
        $model = ProductModel::find($product->id);

        if ($model === null) {
            $newModel = ProductMapper::toModel($product);
            $newModel->version = 1;
            $newModel->deleted_at = null;
            $newModel->save();
            return;
        }

        $affected = ProductModel::where('id', $product->id)
            ->where('version', $model->version)
            ->update([
                'name' => $product->name,
                'price' => $product->price->getAmountAsFloat(),
                'stock' => $product->stock,
                'category_id' => $product->categoryId,
                'image_key' => $product->imageKey,
                'version' => $model->version + 1,
            ]);

        if ($affected === 0) {
            throw new ConcurrencyConflict('Otra operación modificó los datos al mismo tiempo. Inténtalo de nuevo.');
        }
    }

    public function delete(string $id): void
    {
        $model = ProductModel::find($id);
        if ($model === null) {
            return;
        }

        if ($model->deleted_at !== null) {
            return;
        }

        $affected = ProductModel::where('id', $id)
            ->where('version', $model->version)
            ->update([
                'deleted_at' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s.u'),
                'version' => $model->version + 1,
            ]);

        if ($affected === 0) {
            throw new ConcurrencyConflict('Otra operación modificó los datos al mismo tiempo. Inténtalo de nuevo.');
        }
    }
}
