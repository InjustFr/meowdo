<?php

declare(strict_types=1);

namespace App\Fixtures\Story;

use App\Domain\Gamification\Coat;
use App\Domain\Planning\ProjectColor;
use App\Domain\Planning\Quadrant;
use App\Fixtures\Factory\CatFactory;
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
        $user = UserFactory::new()->withPassword('meowdomeowdo')->create([
            'email' => 'other@meowdo.local',
            'displayName' => 'Other',
            'timezone' => 'America/New_York',
            'now' => $now,
        ]);
        PlayerFactory::createOne(['owner' => $user]);
        CatFactory::createOne(['owner' => $user, 'name' => 'Pixel', 'coat' => Coat::Tuxedo]);
        $today = $user->today($now);

        $plans = ProjectFactory::createOne(['owner' => $user, 'name' => 'Secret plans', 'color' => ProjectColor::Ginger, 'now' => $now]);
        TaskFactory::new()->in($plans)->classified(Quadrant::DoFirst)->plannedOn($today)->create(['owner' => $user, 'title' => 'Catch the red dot', 'now' => $now]);
        TaskFactory::new()->in($plans)->plannedOn($today->modify('+1 day'))->create(['owner' => $user, 'title' => 'Knock the vase off the shelf', 'now' => $now]);
        TaskFactory::new()->create(['owner' => $user, 'title' => 'Nap in the sun', 'now' => $now]);
    }
}
