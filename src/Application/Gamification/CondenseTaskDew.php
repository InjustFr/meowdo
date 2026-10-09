<?php

declare(strict_types=1);

namespace App\Application\Gamification;

use App\Domain\Gamification\Greenhouse\DewGain;
use App\Domain\Gamification\Greenhouse\DewPolicy;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use App\Domain\Identity\User;
use App\Domain\Planning\Task;
use Psr\Clock\ClockInterface;

final readonly class CondenseTaskDew
{
    public function __construct(
        private GreenhouseRepository $greenhouses,
        private DewPolicy $policy,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @param list<Task> $rewarded
     */
    public function __invoke(User $owner, array $rewarded): ?DewGain
    {
        if ([] === $rewarded) {
            return null;
        }
        $greenhouse = $this->greenhouses->of($owner);
        $gain = new DewGain(0);
        foreach ($rewarded as $task) {
            $gain = $gain->plus($this->policy->dewFor($task, $greenhouse));
        }

        return $greenhouse->receive($gain, $this->clock->now());
    }
}
