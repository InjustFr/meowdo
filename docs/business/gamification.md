# Gamification

- **G1** Completing a task earns a reward **once per task, ever**: reopening and completing again earns nothing. Deleting a task never removes XP. A subtask is a task: it earns its own reward, and its parent earns one too when the last subtask completes it (S3). — `Task::claimReward()`
- **G2** Base XP by quadrant: Plant 25, Water 20, unsorted 8, Trim 10, Compost 5 (Plant pays most: important work done before it is urgent). +5 when done on or before its deadline. — `RewardPolicy`
- **G3** Streak multiplier: × (1 + 0.05 × streak days, capped at 10 days). — `RewardPolicy`
- **G4** Streak: consecutive days (user's time zone) with at least one rewarded completion. Missing a day resets the shown streak to 0; the best streak is kept. — `Streak`
- **G5** Level n is reached at 50·n·(n−1) XP (L2 = 100, L3 = 300, L4 = 600…). — `LevelCurve`
- **G6** No punishment: nothing is ever lost, collected species included.
- **G7** Herbarium: crossing a level while completing a task collects one moss species per level crossed, drawn at random among the species of the catalogue not collected yet; it is revealed in the level-up celebration. Once every species is collected, levels bring no more species. Levels already reached before the herbarium existed were each given one species when it was introduced. — `SpeciesCatalog`, `SpeciesDraw`, `CollectSpecies`
- **G8** Each species shows an iNaturalist photo under CC0 or CC BY, credited with its author and licence on the card and on `/credits`. Photos and common names are imported once with `bin/console app:herbarium:import` and served by MossyDew, never loaded from iNaturalist at runtime. — `ImportSpeciesCommand`
- **G9** Achievements unlock once, checked after completing and sorting in the matrix. Newly unlocked ones are celebrated once, then marked seen. — `AchievementCheck`, `AchievementRule` classes
