<?php

declare(strict_types=1);

namespace App\Application\Planning\ShowStatistics;

final readonly class ProjectCountView
{
    public function __construct(
        public ?string $id,
        public ?string $name,
        public ?string $color,
        public int $count,
    ) {
    }
}
