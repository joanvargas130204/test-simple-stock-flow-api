<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Application\DTO\PagedResult;
use App\Application\DTO\ProductView;
use App\Application\Model\PageRequest;
use App\Domain\Model\Product;

interface ProductRepository
{
    public function findById(string $id): ?Product;

    public function findByIdActive(string $id): ?Product;

    /**
     * @param string[] $ids
     * @return array<string, Product>
     */
    public function findManyActive(array $ids): array;

    /**
     * @return PagedResult<ProductView>
     */
    public function search(?string $query, ?string $categoryId, PageRequest $pageRequest): PagedResult;

    public function save(Product $product): void;

    public function delete(string $id): void;
}
