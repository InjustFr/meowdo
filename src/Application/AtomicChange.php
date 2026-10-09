<?php

declare(strict_types=1);

namespace App\Application;

interface AtomicChange
{
    /**
     * @template T
     *
     * @param callable(): T $change
     *
     * @return T
     */
    public function apply(callable $change): mixed;
}
