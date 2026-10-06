import { quadrantPriority } from './quadrants.js';

const byNullableAscending = (a, b) => {
    if (a === b) return 0;
    if (a === null || a === undefined) return 1;
    if (b === null || b === undefined) return -1;
    return a < b ? -1 : 1;
};

export function compareTasks(a, b) {
    return Number(a.done) - Number(b.done)
        || quadrantPriority(a.quadrant) - quadrantPriority(b.quadrant)
        || a.rank - b.rank
        || byNullableAscending(a.dueOn, b.dueOn)
        || byNullableAscending(a.createdAt, b.createdAt)
        || byNullableAscending(a.id, b.id);
}

export function sortTasks(tasks) {
    return [...tasks].sort(compareTasks);
}
