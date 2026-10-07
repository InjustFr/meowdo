# Tasks, projects and the matrix

- **T1** A task has a title (required, ≤ 200 chars, whitespace collapsed), optional notes, an optional project, an optional **planned day** (`plannedOn`: the day I intend to do it) and an optional **deadline** (`dueOn`). — `Task`
- **T2** Planning shortcuts: Today, Tomorrow, Next week (next Monday), a picked date, or not planned. Resolved on the server in the **user's time zone**. — `PlanShortcut`, `PlanTaskHandler`
- **T3** **Today** is not a project: it lists open tasks from every project (and the inbox) planned on or before today, or due on or before today, plus tasks completed today. Tasks planned on an earlier day and not due yet appear under **From earlier**; "Move all to today" plans them for today. — `ListTodayTasksHandler`, `MoveOverdueToTodayHandler`
- **T4** A project page shows all its open tasks whatever their planned day, plus tasks done in the last 14 days. Adding a task to Today from there only sets its planned day; it stays in its project. — `ListProjectTasksHandler`
- **T5** **Upcoming** shows open tasks planned over 7 days, starting today (or a chosen day). — `ListUpcomingTasksHandler`
- **T6** **Inbox** = open tasks without a project. — `ListInboxTasksHandler`
- **T7** Deleting a project keeps its tasks, which move to the inbox. — `Task.project` `onDelete: SET NULL`
- **T8** Project names are unique per user (case-insensitive), ≤ 60 chars, with one of 8 colours (berry, honey, moss, fjord, heather, blossom, lichen, rust). — `CreateProjectHandler`, `EditProjectHandler`, `ProjectColor`

## Eisenhower matrix

- **M1** Quadrants: Water = urgent & important (`DoFirst`), Plant = important, not urgent (`Schedule`), Trim = urgent, not important (`Delegate`), Compost = neither (`Eliminate`). A task may be unsorted.
- **M2** Inside a quadrant, tasks have a rank set by drag and drop; a task newly put in a quadrant goes last. Done tasks cannot be reordered. — `ReorderQuadrantHandler`, `ClassifyTaskHandler`
- **M3** **Ordering rule of every list**: open before done → Water, Plant, Trim, Compost, unsorted → rank → deadline (none last) → creation. — `DoctrineTaskQueries::ordered()`, mirrored by `assets/vue/tasks/compareTasks.js`
