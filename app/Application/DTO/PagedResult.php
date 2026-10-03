<?php

declare(strict_types=1);

namespace App\Application\DTO;

/**
 * @template T
 */
final readonly class PagedResult
{
    public int $totalPages;

    /**
     * @param array<T> $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $size,
        public int $total,
    ) {
        $this->totalPages = $this->size === 0 ? 0 : (int) ceil($this->total / $this->size);
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(function ($item) {
                if (is_object($item) && method_exists($item, 'toArray')) {
                    return $item->toArray();
                }
                return $item;
            }, $this->items),
            'page' => $this->page,
            'size' => $this->size,
            'total' => $this->total,
            'totalPages' => $this->totalPages,
        ];
    }
}
