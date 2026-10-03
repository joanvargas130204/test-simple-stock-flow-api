<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\DTO\CreateProductCommand;
use App\Application\DTO\UpdateProductCommand;
use App\Application\Exception\ConcurrencyConflict;
use App\Application\Model\PageRequest;
use App\Application\Ports\Inbound\ManageProducts;
use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\Exception\ProductNotFoundException;
use App\Presentation\Http\ProblemDetails\EmptyErrorRenderer;
use App\Presentation\Http\ProblemDetails\ProblemDetailsRenderer;
use App\Presentation\Http\ProblemDetails\ValidationErrorRenderer;
use App\Presentation\Http\Request\StoreProductRequest;
use App\Presentation\Http\Request\UpdateProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProductController
{
    private const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    public function __construct(
        private readonly ManageProducts $manageProducts,
    ) {
    }

    public function index(Request $request): Response
    {
        $errors = [];

        // Validate page
        $rawPage = $request->query('page');
        $page = 1;
        if ($rawPage !== null) {
            if (! is_numeric($rawPage) || (int) $rawPage != $rawPage) {
                $errors['page'] = ['Value is not a valid integer'];
            } else {
                $page = (int) $rawPage;
            }
        }

        // Validate size
        $rawSize = $request->query('size');
        $size = 20;
        if ($rawSize !== null) {
            if (! is_numeric($rawSize) || (int) $rawSize != $rawSize) {
                $errors['size'] = ['Value is not a valid integer'];
            } else {
                $size = (int) $rawSize;
            }
        }

        // Validate categoryId
        $categoryId = $request->query('categoryId');
        if ($categoryId !== null && $categoryId !== '') {
            if (! preg_match(self::UUID_REGEX, (string) $categoryId)) {
                $errors['categoryId'] = ['Input should be a valid UUID'];
            }
        } else {
            $categoryId = null;
        }

        if (! empty($errors)) {
            return ValidationErrorRenderer::render($errors);
        }

        $search = $request->query('search');
        if ($search !== null && trim((string) $search) === '') {
            $search = null;
        }

        $pageRequest = new PageRequest($page, $size);
        $result = $this->manageProducts->searchProducts($search, $categoryId, $pageRequest);

        return response()->json($result->toArray(), 200);
    }

    public function show(string $id): Response
    {
        if (! preg_match(self::UUID_REGEX, $id)) {
            return EmptyErrorRenderer::notFound();
        }

        try {
            $productView = $this->manageProducts->getProduct($id);

            return response()->json($productView->toArray(), 200);
        } catch (ProductNotFoundException) {
            return EmptyErrorRenderer::notFound();
        }
    }

    public function store(StoreProductRequest $request): Response
    {
        try {
            $productId = $this->manageProducts->createProduct(new CreateProductCommand(
                name: (string) $request->input('name'),
                price: (float) $request->input('price'),
                stock: (int) $request->input('stock'),
                categoryId: (string) $request->input('categoryId')
            ));

            return response()->json(['id' => $productId], 201, [
                'Location' => "/api/products/{$productId}",
            ]);
        } catch (BusinessRuleViolation $e) {
            return ProblemDetailsRenderer::ruleViolation($e->getMessage());
        }
    }

    public function update(string $id, UpdateProductRequest $request): Response
    {
        if (! preg_match(self::UUID_REGEX, $id)) {
            return EmptyErrorRenderer::notFound();
        }

        try {
            $this->manageProducts->updateProduct(new UpdateProductCommand(
                id: $id,
                name: (string) $request->input('name'),
                price: (float) $request->input('price'),
                stock: (int) $request->input('stock'),
                categoryId: (string) $request->input('categoryId')
            ));

            return response('', 204);
        } catch (ProductNotFoundException) {
            return EmptyErrorRenderer::notFound();
        } catch (ConcurrencyConflict $e) {
            return ProblemDetailsRenderer::concurrencyConflict($e->getMessage());
        } catch (BusinessRuleViolation $e) {
            return ProblemDetailsRenderer::ruleViolation($e->getMessage());
        }
    }

    public function destroy(string $id): Response
    {
        if (! preg_match(self::UUID_REGEX, $id)) {
            return EmptyErrorRenderer::notFound();
        }

        try {
            $this->manageProducts->deleteProduct($id);

            return response('', 204);
        } catch (ProductNotFoundException) {
            return EmptyErrorRenderer::notFound();
        } catch (ConcurrencyConflict $e) {
            return ProblemDetailsRenderer::concurrencyConflict($e->getMessage());
        }
    }

    public function uploadImage(string $id, Request $request): Response
    {
        if (! preg_match(self::UUID_REGEX, $id)) {
            return EmptyErrorRenderer::notFound();
        }

        if (! $request->hasFile('file')) {
            return ValidationErrorRenderer::render(['file' => ['Field required']]);
        }

        $file = $request->file('file');
        if ($file === null || ! $file->isValid()) {
            return ValidationErrorRenderer::render(['file' => ['File upload failed']]);
        }

        $binaryData = file_get_contents($file->getRealPath());
        if ($binaryData === false) {
            return ValidationErrorRenderer::render(['file' => ['Cannot read uploaded file']]);
        }

        $mimeType = $file->getMimeType() ?? '';

        try {
            $imageUrl = $this->manageProducts->uploadImage($id, $binaryData, $mimeType);

            return response()->json(['url' => $imageUrl], 200);
        } catch (ProductNotFoundException) {
            return EmptyErrorRenderer::notFound();
        } catch (BusinessRuleViolation $e) {
            return ProblemDetailsRenderer::ruleViolation($e->getMessage());
        }
    }
}
