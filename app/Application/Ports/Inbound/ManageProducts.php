<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\DTO\CreateProductCommand;
use App\Application\DTO\PagedResult;
use App\Application\DTO\ProductView;
use App\Application\DTO\UpdateProductCommand;
use App\Application\Model\PageRequest;

interface ManageProducts
{
    /**
     * @return PagedResult<ProductView>
     */
    public function searchProducts(?string $query, ?string $categoryId, PageRequest $pageRequest): PagedResult;

    public function getProduct(string $id): ProductView;

    public function createProduct(CreateProductCommand $command): string;

    public function updateProduct(UpdateProductCommand $command): void;

    public function deleteProduct(string $id): void;

    public function uploadImage(string $productId, string $binaryData, string $mimeType): string;

    /**
     * @return array<array{id: string, name: string}>
     */
    public function listCategories(): array;
}
