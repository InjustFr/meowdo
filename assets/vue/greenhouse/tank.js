const MILLI = 1000;
const SECONDS_PER_HOUR = 3600;

export function tankMilliAt(view, elapsedMs) {
    const seconds = Math.max(0, Math.floor(elapsedMs / 1000));
    return Math.min(view.capacity * MILLI, view.tankMilli + Math.floor((view.rateMilliPerHour * seconds) / SECONDS_PER_HOUR));
}

export function tankAt(view, elapsedMs) {
    return Math.floor(tankMilliAt(view, elapsedMs) / MILLI);
}

export function fillRatio(view, elapsedMs) {
    return view.capacity > 0 ? tankMilliAt(view, elapsedMs) / (view.capacity * MILLI) : 0;
}

export function secondsUntilFull(view, elapsedMs) {
    const missing = view.capacity * MILLI - tankMilliAt(view, elapsedMs);
    if (missing <= 0) return 0;
    if (view.rateMilliPerHour <= 0) return null;
    return Math.ceil((missing * SECONDS_PER_HOUR) / view.rateMilliPerHour);
}

export function ratePerHour(rateMilliPerHour) {
    return Math.floor(rateMilliPerHour / 100) / 10;
}
