<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\PlaceSaleCommand;
use App\Application\Exception\ConcurrencyConflict;
use App\Application\Ports\Inbound\PlaceSale;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Application\Ports\Outbound\Clock;
use App\Application\Ports\Outbound\ProductRepository;
use App\Application\Ports\Outbound\SaleRepository;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Domain\Exception\EmptySaleException;
use App\Domain\Exception\InsufficientStockException;
use App\Domain\Exception\InvalidQuantityException;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Exception\RepeatedProductException;
use App\Domain\Model\Sale;
use App\Domain\ValueObject\Quantity;

final readonly class PlaceSaleService implements PlaceSale
{
    private const MAX_RETRIES = 3;

    public function __construct(
        private ProductRepository $productRepository,
        private SaleRepository $saleRepository,
        private CategoryRepository $categoryRepository,
        private UnitOfWork $unitOfWork,
        private Clock $clock,
    ) {
    }

    public function execute(PlaceSaleCommand $command): string
    {
        // 1. lines non-empty check
        if (empty($command->lines)) {
            throw new EmptySaleException('La venta debe tener al menos un ítem.');
        }

        // 2. repeated product check
        $seenProductIds = [];
        foreach ($command->lines as $line) {
            if (isset($seenProductIds[$line->productId])) {
                throw new RepeatedProductException('La venta tiene productos repetidos.');
            }
            $seenProductIds[$line->productId] = true;
        }

        $attempts = 0;
        while ($attempts < self::MAX_RETRIES) {
            $attempts++;
            try {
                return $this->unitOfWork->run(function () use ($command): string {
                    $saleId = self::generateUuid();
                    $sale = new Sale(
                        id: $saleId,
                        soldAt: $this->clock->now(),
                        soldByUsername: $command->soldByUsername,
                        soldByUserId: $command->soldByUserId
                    );

                    // 3. Check products existence (active only)
                    $productMap = [];
                    foreach ($command->lines as $line) {
                        $product = $this->productRepository->findByIdActive($line->productId);
                        if ($product === null) {
                            throw new ProductNotFoundException("El producto {$line->productId} no existe.");
                        }
                        $productMap[$line->productId] = $product;
                    }

                    // 4. Quantity > 0 check & 5. Stock sufficiency check
                    foreach ($command->lines as $line) {
                        if ($line->quantity <= 0) {
                            throw new InvalidQuantityException('La cantidad debe ser mayor a cero.');
                        }
                        $product = $productMap[$line->productId];
                        if ($product->stock < $line->quantity) {
                            throw new InsufficientStockException(
                                "Stock insuficiente para '{$product->name}': disponible {$product->stock}, solicitado {$line->quantity}."
                            );
                        }
                    }

                    // Process lines and persist
                    foreach ($command->lines as $line) {
                        $product = $productMap[$line->productId];
                        $category = $this->categoryRepository->findById($product->categoryId);
                        $categoryName = $category?->name ?? 'General';

                        $sale->addItem(
                            $product,
                            new Quantity($line->quantity),
                            $categoryName
                        );

                        $this->productRepository->save($product);
                    }

                    $this->saleRepository->save($sale);

                    return $sale->id;
                });
            } catch (ConcurrencyConflict $e) {
                if ($attempts >= self::MAX_RETRIES) {
                    throw $e;
                }
                // Backoff slightly before retry (10ms)
                usleep(10000);
            }
        }

        throw new ConcurrencyConflict('Otra operación modificó los datos al mismo tiempo. Inténtalo de nuevo.');
    }

    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
