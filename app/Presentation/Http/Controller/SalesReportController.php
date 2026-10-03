<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Model\DateRange;
use App\Application\Ports\Inbound\GetSalesReport;
use App\Domain\Exception\BusinessRuleViolation;
use App\Presentation\Http\ProblemDetails\ProblemDetailsRenderer;
use App\Presentation\Http\ProblemDetails\ValidationErrorRenderer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SalesReportController
{
    private const ISO8601_STRICT_REGEX = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:\d{2})$/';

    public function __construct(
        private readonly GetSalesReport $getSalesReport,
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

        if (! empty($errors)) {
            return ValidationErrorRenderer::render($errors);
        }

        try {
            $range = DateRange::fromStrings((string) $rawFrom, (string) $rawTo);
            $report = $this->getSalesReport->execute($range);

            return response()->json($report->toArray(), 200);
        } catch (BusinessRuleViolation $e) {
            return ProblemDetailsRenderer::ruleViolation($e->getMessage());
        }
    }
}
