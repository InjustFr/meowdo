<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement;

final readonly class AchievementReferee
{
    /** @var list<AchievementRule> */
    private array $rules;

    /**
     * @param iterable<AchievementRule> $rules
     */
    public function __construct(iterable $rules)
    {
        $this->rules = array_values([...$rules]);
    }

    /**
     * @return list<AchievementRule>
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * @param list<string> $alreadyUnlocked
     *
     * @return list<AchievementRule>
     */
    public function newlyMet(PlayerStats $stats, array $alreadyUnlocked): array
    {
        return array_values(array_filter(
            $this->rules,
            static fn (AchievementRule $rule): bool => !\in_array($rule->id(), $alreadyUnlocked, true) && $rule->isMetBy($stats),
        ));
    }
}
