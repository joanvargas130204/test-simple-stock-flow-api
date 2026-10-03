<?php

declare(strict_types=1);

namespace App\Application\Model;

final readonly class PageRequest
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_SIZE = 20;
    public const MAX_SIZE = 100;

    private int $page;
    private int $size;

    public function __construct(?int $page = null, ?int $size = null)
    {
        $resolvedPage = ($page === null || $page < 1) ? self::DEFAULT_PAGE : $page;

        if ($size === null || $size < 1) {
            $resolvedSize = self::DEFAULT_SIZE;
        } elseif ($size > self::MAX_SIZE) {
            $resolvedSize = self::MAX_SIZE;
        } else {
            $resolvedSize = $size;
        }

        $this->page = $resolvedPage;
        $this->size = $resolvedSize;
    }

    public static function of(?int $page = null, ?int $size = null): self
    {
        return new self($page, $size);
    }

    public function page(): int
    {
        return $this->page;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->size;
    }

    public function limit(): int
    {
        return $this->size;
    }
}
