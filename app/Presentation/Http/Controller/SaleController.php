<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\DTO\PlaceSaleCommand;
use App\Application\DTO\PlaceSaleItemCommand;
use App\Application\Exception\ConcurrencyConflict;
use App\Application\Model\DateRange;
use App\Application\Model\PageRequest;
use App\Application\Ports\Inbound\GetSales;
use App\Application\Ports\Inbound\PlaceSale;
use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\Exception\SaleNotFoundException;
use App\Presentation\Http\ProblemDetails\EmptyErrorRenderer;
use App\Presentation\Http\ProblemDetails\ProblemDetailsRenderer;
use App\Presentation\Http\ProblemDetails\ValidationErrorRenderer;
use App\Presentation\Http\Request\PlaceSaleRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SaleController
{
    private const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    private const ISO8601_STRICT_REGEX = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:\d{2})$/';

    public function __construct(
        private readonly PlaceSale $placeSale,
        private readonly GetSales $getSales,
    ) {
    }

    public function index(Request $request): Response
    {
        $errors = [];

        $rawFrom = $request->query('from');
        $rawTo = $request->query('to');

        if ($rawFrom === null || $rawFrom === '') {
            $errors['from'] = ['Field required'];
        } elseif (! is_string($rawFrom) || ! preg_match(self::ISO8601_STRICT_REGEX, $rawFrom)) {
            $errors['from'] = ['Input should be a valid datetime with explicit timezone displacement'];
        }

        if ($rawTo === null || $rawTo === '') {
            $errors['to'] = ['Field required'];
        } elseif (! is_string($rawTo) || ! preg_match(self::ISO8601_STRICT_REGEX, $rawTo)) {
            $errors['to'] = ['Input should be a valid datetime with explicit timezone displacement'];
        }

        $rawPage = $request->query('page');
        $page = 1;
        if ($rawPage !== null) {
            if (! is_numeric($rawPage) || (int) $rawPage != $rawPage) {
                $errors['page'] = ['Value is not a valid integer'];
            } else {
                $page = (int) $rawPage;
            }
        }

        $rawSize = $request->query('size');
        $size = 20;
        if ($rawSize !== null) {
            if (! is_numeric($rawSize) || (int) $rawSize != $rawSize) {
                $errors['size'] = ['Value is not a valid integer'];
            } else {
                $size = (int) $rawSize;
            }
        }

        if (! empty($errors)) {
            return ValidationErrorRenderer::render($errors);
        }

        try {
            $range = DateRange::fromStrings((string) $rawFrom, (string) $rawTo);
            $pageRequest = new PageRequest($page, $size);

            $result = $this->getSales->search($range, $pageRequest);

            return response()->json($result->toArray(), 200);
        } catch (BusinessRuleViolation $e) {
            return ProblemDetailsRenderer::ruleViolation($e->getMessage());
        }
    }

    public function show(string $id): Response
    {
        if (! preg_match(self::UUID_REGEX, $id)) {
            return EmptyErrorRenderer::notFound();
        }

        try {
            $saleView = $this->getSales->getById($id);

            return response()->json($saleView->toArray(), 200);
        } catch (SaleNotFoundException) {
            return EmptyErrorRenderer::notFound();
        }
    }

    public function store(PlaceSaleRequest $request): Response
    {
        $userId = (string) $request->attributes->get('auth_user_id');
        $username = (string) $request->attributes->get('auth_username');

        $rawLines = $request->input('lines', []);
        $commandLines = [];

        foreach ($rawLines as $line) {
            $commandLines[] = new PlaceSaleItemCommand(
                productId: (string) ($line['productId'] ?? ''),
                quantity: (int) ($line['quantity'] ?? 0)
            );
        }

        try {
            $saleId = $this->placeSale->execute(new PlaceSaleCommand(
                lines: $commandLines,
                soldByUserId: $userId,
                soldByUsername: $username
            ));

            return response()->json(['id' => $saleId], 201, [
                'Location' => "/api/sales/{$saleId}",
            ]);
        } catch (ConcurrencyConflict $e) {
            return ProblemDetailsRenderer::concurrencyConflict($e->getMessage());
        } catch (BusinessRuleViolation $e) {
            return ProblemDetailsRenderer::ruleViolation($e->getMessage());
        }
    }
}
