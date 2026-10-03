<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

interface UnitOfWork
{
    /**
     * Executes the given operation inside an isolated database transaction.
     *
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public function run(callable $operation): mixed;
}
