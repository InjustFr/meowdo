<?php

declare(strict_types=1);

namespace App\Fixtures\Story;

use App\Domain\Gamification\Tint;
use App\Domain\Planning\ProjectColor;
use App\Domain\Planning\Quadrant;
use App\Fixtures\Factory\CritterFactory;
use App\Fixtures\Factory\PlayerFactory;
use App\Fixtures\Factory\ProjectFactory;
use App\Fixtures\Factory\TaskFactory;
use App\Fixtures\Factory\UserFactory;
use Psr\Clock\ClockInterface;
use Zenstruck\Foundry\Story;

final class OtherUserStory extends Story
{
    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function build(): void
    {
        $now = $this->clock->now();
        $user = UserFactory::createOne([
            'accountId' => 'other',
            'email' => 'other@mossydew.local',
            'displayName' => 'Other',
            'timezone' => 'America/New_York',
            'now' => $now,
        ]);
        PlayerFactory::createOne(['owner' => $user]);
        CritterFactory::createOne(['owner' => $user, 'name' => 'Bramble', 'tint' => Tint::Peat]);
        $today = $user->today($now);

        $plans = ProjectFactory::createOne(['owner' => $user, 'name' => 'Secret plans', 'color' => ProjectColor::Rust, 'now' => $now]);
        TaskFactory::new()->in($plans)->classified(Quadrant::DoFirst)->plannedOn($today)->create(['owner' => $user, 'title' => 'Find the hidden spring', 'now' => $now]);
        TaskFactory::new()->in($plans)->plannedOn($today->modify('+1 day'))->create(['owner' => $user, 'title' => 'Roll the pebble down the hill', 'now' => $now]);
        TaskFactory::new()->create(['owner' => $user, 'title' => 'Bask in the morning dew', 'now' => $now]);
    }
}
