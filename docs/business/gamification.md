# Gamification

- **G1** Completing a task earns a reward **once per task, ever**: reopening and completing again earns nothing. Deleting a task never removes XP. — `Task::claimReward()`
- **G2** Base XP by quadrant: Plant 25, Water 20, unsorted 8, Trim 10, Compost 5 (Plant pays most: important work done before it is urgent). +5 when done on or before its deadline. — `RewardPolicy`
- **G3** Streak multiplier: × (1 + 0.05 × streak days, capped at 10 days). Coins = ⌈XP / 4⌉. — `RewardPolicy`
- **G4** Streak: consecutive days (user's time zone) with at least one rewarded completion. Missing a day resets the shown streak to 0; the best streak is kept. — `Streak`
- **G5** Level n is reached at 50·n·(n−1) XP (L2 = 100, L3 = 300, L4 = 600…). — `LevelCurve`
- **G6** No punishment: nothing is ever lost. The critter's mood only reflects activity: lively when something was done today, idle otherwise, dormant (curled up like a tardigrade in its tun) after 3 days without completing anything (or before the first completion). — `CritterMood`
- **G7** Shop: cosmetics for 4 slots (hat, neckwear, toy, backdrop) with a coin price and a minimum level; buying one puts it on the critter. Owned items can be worn or taken off freely. The tint (sprout, lichen, peat, rust, frost, plum) is free to change. — `CosmeticCatalog`, `Player::buy()`, `Critter::wear()`
- **G8** Achievements unlock once, checked after completing, sorting in the matrix and buying. Newly unlocked ones are celebrated once, then marked seen. — `AchievementCheck`, `AchievementRule` classes
