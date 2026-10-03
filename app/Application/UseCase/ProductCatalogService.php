<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\CreateProductCommand;
use App\Application\DTO\PagedResult;
use App\Application\DTO\ProductView;
use App\Application\DTO\UpdateProductCommand;
use App\Application\Model\PageRequest;
use App\Application\Ports\Inbound\ManageProducts;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Application\Ports\Outbound\FileStorage;
use App\Application\Ports\Outbound\ProductRepository;
use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\Exception\InvalidPriceException;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Exception\UnknownCategoryException;
use App\Domain\Model\Category;
use App\Domain\Model\Product;
use App\Domain\ValueObject\Money;

final readonly class ProductCatalogService implements ManageProducts
{
    private const MAX_IMAGE_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private FileStorage $fileStorage,
    ) {
    }

    public function searchProducts(?string $query, ?string $categoryId, PageRequest $pageRequest): PagedResult
    {
        return $this->productRepository->search($query, $categoryId, $pageRequest);
    }

    public function getProduct(string $id): ProductView
    {
        $product = $this->productRepository->findById($id);
        if ($product === null) {
            throw new ProductNotFoundException("El producto {$id} no existe.");
        }

        $category = $this->categoryRepository->findById($product->categoryId);
        $categoryName = $category?->name ?? 'General';

        return new ProductView(
            id: $product->id,
            name: $product->name,
            price: $product->price->getAmountAsFloat(),
            currency: $product->price->currency,
            stock: $product->stock,
            categoryId: $product->categoryId,
            categoryName: $categoryName,
            imageUrl: $this->fileStorage->getUrl($product->imageKey),
        );
    }

    public function createProduct(CreateProductCommand $command): string
    {
        // 1. Validate Category Existence First
        if ($command->categoryId === '00000000-0000-0000-0000-000000000000') {
            throw new BusinessRuleViolation('La categoría es obligatoria.');
        }

        $category = $this->categoryRepository->findById($command->categoryId);
        if ($category === null) {
            throw new UnknownCategoryException("La categoría {$command->categoryId} no existe.");
        }

        // 2. Name validation
        if (trim($command->name) === '') {
            throw new BusinessRuleViolation('El nombre del producto es obligatorio.');
        }

        // 3. Price validation
        if ($command->price <= 0) {
            throw new InvalidPriceException('El precio debe ser mayor a cero.');
        }

        // 4. Stock validation
        if ($command->stock < 0) {
            throw new BusinessRuleViolation('El stock inicial no puede ser negativo.');
        }

        $product = new Product(
            id: self::generateUuid(),
            name: $command->name,
            price: Money::of($command->price),
            stock: $command->stock,
            categoryId: $command->categoryId,
            imageKey: null
        );

        $this->productRepository->save($product);

        return $product->id;
    }

    public function updateProduct(UpdateProductCommand $command): void
    {
        $product = $this->productRepository->findById($command->id);
        if ($product === null) {
            throw new ProductNotFoundException("El producto {$command->id} no existe.");
        }

        if ($command->categoryId === '00000000-0000-0000-0000-000000000000') {
            throw new BusinessRuleViolation('La categoría es obligatoria.');
        }

        $category = $this->categoryRepository->findById($command->categoryId);
        if ($category === null) {
            throw new UnknownCategoryException("La categoría {$command->categoryId} no existe.");
        }

        if (trim($command->name) === '') {
            throw new BusinessRuleViolation('El nombre del producto es obligatorio.');
        }

        if ($command->price <= 0) {
            throw new InvalidPriceException('El precio debe ser mayor a cero.');
        }

        if ($command->stock < 0) {
            throw new BusinessRuleViolation('El stock inicial no puede ser negativo.');
        }

        $product->rename($command->name);
        $product->changePrice(Money::of($command->price));
        $product->setCategory($command->categoryId);
        $product->adjustStock($command->stock);

        $this->productRepository->save($product);
    }

    public function deleteProduct(string $id): void
    {
        $product = $this->productRepository->findById($id);
        if ($product === null) {
            throw new ProductNotFoundException("El producto {$id} no existe.");
        }

        if ($product->imageKey !== null) {
            $oldKey = $product->imageKey;
            $product->detachImage();
            $this->productRepository->save($product);
            $this->fileStorage->delete($oldKey);
        }

        $this->productRepository->delete($id);
    }

    public function uploadImage(string $productId, string $binaryData, string $mimeType): string
    {
        $product = $this->productRepository->findById($productId);
        if ($product === null) {
            throw new ProductNotFoundException("El producto {$productId} no existe.");
        }

        if (! in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new BusinessRuleViolation("Tipo de archivo no permitido: {$mimeType}.");
        }

        if (strlen($binaryData) > self::MAX_IMAGE_SIZE_BYTES) {
            throw new BusinessRuleViolation('La imagen supera el máximo de 5 MB.');
        }

        $oldKey = $product->imageKey;
        $saved = $this->fileStorage->save($binaryData, $mimeType);

        $product->attachImage($saved['key']);
        $this->productRepository->save($product);

        if ($oldKey !== null) {
            $this->fileStorage->delete($oldKey);
        }

        return $saved['url'];
    }

    public function listCategories(): array
    {
        $categories = $this->categoryRepository->findAll();

        return array_map(fn (Category $c) => [
            'id' => $c->id,
            'name' => $c->name,
        ], $categories);
    }

    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
