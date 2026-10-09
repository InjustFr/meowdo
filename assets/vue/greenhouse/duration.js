const MINUTE = 60;
const HOUR = 60 * MINUTE;
const DAY = 24 * HOUR;

export function duration(seconds) {
    const minutesLeft = Math.max(1, Math.ceil(seconds / MINUTE));
    const days = Math.floor(minutesLeft / (DAY / MINUTE));
    const hours = Math.floor((minutesLeft % (DAY / MINUTE)) / (HOUR / MINUTE));
    const minutes = minutesLeft % (HOUR / MINUTE);
    if (days > 0) return { unit: 'days', days, hours, minutes };
    if (hours > 0) return { unit: 'hours', days, hours, minutes };
    return { unit: 'minutes', days, hours, minutes };
}
