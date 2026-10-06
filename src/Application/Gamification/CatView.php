<?php

declare(strict_types=1);

namespace App\Application\Gamification;

use App\Domain\Gamification\Cat;
use App\Domain\Gamification\CatMood;

final readonly class CatView
{
    /**
     * @param array{hat: ?string, neckwear: ?string, toy: ?string, backdrop: ?string} $outfit
     */
    private function __construct(
        public string $name,
        public string $coat,
        public string $mood,
        public array $outfit,
    ) {
    }

    public static function of(Cat $cat, CatMood $mood): self
    {
        return new self($cat->name(), $cat->coat()->value, $mood->value, $cat->outfit());
    }
}
