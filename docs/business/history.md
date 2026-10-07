# Done and statistics

Every day below is a day of the **user's time zone**: a task completed at 23:30 in Paris counts for that Paris day, whatever the server clock says. Only tasks currently done count; reopening a task removes it from every figure. Another user's tasks are never counted.

## Done

- **H1** **Done** lists completed tasks over a window of 30 days ending the day before `before` (default: tomorrow, so the window ends today), grouped by completion day: newest day first, newest task first inside a day; days without a completion are left out. — `ListDoneTasksHandler`, `DoneTasksTest`
- **H2** `older` is the `before` of the next window: the day after the most recent completion older than the window, so "Show older" never opens an empty month. It is null when nothing older was completed. — `ListDoneTasksHandler`, `TaskQueries::lastCompletedBefore()`
- **H3** Reopening a task from Done takes it off the list; it earns nothing again when completed later (G1).

## Statistics

- **H4** Totals: **completed** = all-time count of done tasks; **this week** = completed since Monday 00:00 (ISO week); **this month** = completed since the 1st at 00:00; **open** = tasks not done; **overdue** = open tasks whose deadline is before today. — `ShowStatisticsHandler`, `StatisticsQueries`
- **H5** **Streak** and **best streak** are the player's (G4): the shown streak drops to 0 after a day without a rewarded completion.
- **H6** The other figures cover the **last 30 days, today included**: completions per day (every day listed, zeros included), per quadrant (Water, Plant, Trim, Compost, unsorted, by the task's current quadrant), per project (current project, inbox included, most completions first, then by name, inbox last on a tie). — `RecentCompletions`, `StatisticsTest`
- **H7** **Deadlines met**: among tasks completed in the last 30 days that have a deadline, a task is on time when its completion day is on or before its deadline, late otherwise. Tasks without a deadline are left out. — `RecentCompletions::onTime()`
