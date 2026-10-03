<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Application\DTO\SalesReport;
use App\Application\DTO\SalesReportRow;
use App\Application\Model\DateRange;
use App\Application\Ports\Outbound\SalesReportQuery;
use Illuminate\Support\Facades\DB;

final class MySqlSalesReportQuery implements SalesReportQuery
{
    public function execute(DateRange $range): SalesReport
    {
        $fromStr = $range->from->format('Y-m-d H:i:s.u');
        $toStr = $range->to->format('Y-m-d H:i:s.u');

        // 1. Sales count
        $salesCount = DB::table('sale')
            ->where('sold_at', '>=', $fromStr)
            ->where('sold_at', '<', $toStr)
            ->count();

        if ($salesCount === 0) {
            return new SalesReport(
                from: $range->fromIso,
                to: $range->toIso,
                salesCount: 0,
                grandTotal: 0.0,
                currency: 'COP',
                rows: []
            );
        }

        // 2. Aggregated rows (grouped by product_id, product_name, category_name)
        $rawRows = DB::table('sale')
            ->join('sale_item', 'sale.id', '=', 'sale_item.sale_id')
            ->where('sale.sold_at', '>=', $fromStr)
            ->where('sale.sold_at', '<', $toStr)
            ->select([
                'sale_item.product_id',
                'sale_item.product_name',
                'sale_item.category_name',
                DB::raw('CAST(SUM(sale_item.quantity) AS SIGNED) AS units_sold'),
                DB::raw('CAST(SUM(sale_item.quantity * sale_item.unit_price) AS DECIMAL(14,2)) AS revenue'),
            ])
            ->groupBy(['sale_item.product_id', 'sale_item.product_name', 'sale_item.category_name'])
            ->orderByRaw('revenue DESC')
            ->get();

        $rows = [];
        $grandTotal = 0.0;

        foreach ($rawRows as $r) {
            $revenue = (float) $r->revenue;
            $grandTotal += $revenue;

            $rows[] = new SalesReportRow(
                productId: (string) $r->product_id,
                productName: (string) $r->product_name,
                categoryName: (string) $r->category_name,
                unitsSold: (int) $r->units_sold,
                revenue: round($revenue, 2)
            );
        }

        return new SalesReport(
            from: $range->fromIso,
            to: $range->toIso,
            salesCount: $salesCount,
            grandTotal: round($grandTotal, 2),
            currency: 'COP',
            rows: $rows
        );
    }
}
