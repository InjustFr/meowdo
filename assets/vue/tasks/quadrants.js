export const QUADRANTS = [
    { value: 'do_first', priority: 0, urgent: true, important: true },
    { value: 'schedule', priority: 1, urgent: false, important: true },
    { value: 'delegate', priority: 2, urgent: true, important: false },
    { value: 'eliminate', priority: 3, urgent: false, important: false },
];

export const UNSORTED_PRIORITY = 4;

export function quadrantPriority(quadrant) {
    return QUADRANTS.find((known) => known.value === quadrant)?.priority ?? UNSORTED_PRIORITY;
}
