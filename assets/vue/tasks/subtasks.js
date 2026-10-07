const byCreation = (a, b) => (a.createdAt === b.createdAt ? a.id.localeCompare(b.id) : a.createdAt.localeCompare(b.createdAt));

export function hasOpenSubtasks(task) {
    return !task.done && task.subtasksDone < task.subtaskCount;
}

export function nestSubtasks(tasks) {
    const listed = new Set(tasks.map((task) => task.id));
    const isNested = (task) => task.parentId !== null && task.parentId !== undefined && listed.has(task.parentId);
    const children = new Map();
    tasks.filter(isNested).forEach((task) => {
        children.set(task.parentId, [...(children.get(task.parentId) ?? []), task]);
    });

    return tasks
        .filter((task) => !isNested(task))
        .map((task) => ({ task, subtasks: (children.get(task.id) ?? []).sort(byCreation) }));
}
