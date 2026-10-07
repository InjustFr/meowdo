export const RECURRENCE_UNITS = ['day', 'week', 'month', 'year'];
export const MIN_INTERVAL = 1;
export const MAX_INTERVAL = 365;

export function parseInterval(text) {
    const value = String(text ?? '').trim();
    if (!/^\d+$/.test(value)) return null;
    const interval = Number(value);
    return interval >= MIN_INTERVAL && interval <= MAX_INTERVAL ? interval : null;
}

export function recurrenceLabel(recurrence, t) {
    if (!recurrence) return null;
    return t(`tasks.repeat.every.${recurrence.unit}`, recurrence.interval);
}
