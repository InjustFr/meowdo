export const INBOX = 'inbox';

export function parseSelection(query) {
    if (typeof query !== 'string' || query === '') return [];
    return [...new Set(query.split(',').filter(Boolean))];
}

export function formatSelection(selection) {
    return selection.length ? selection.join(',') : undefined;
}

export function matchesSelection(task, selection) {
    return selection.length === 0 || selection.includes(task.projectId ?? INBOX);
}

export function mergeOrder(allIds, visibleIds) {
    const visible = new Set(visibleIds);
    const queue = [...visibleIds];
    const order = allIds.map((id) => (visible.has(id) ? queue.shift() : id));
    return [...order, ...queue].filter(Boolean);
}
