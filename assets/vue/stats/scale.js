const STEPS = [1, 2, 5];

export function niceStep(raw) {
    if (raw <= 1) return 1;
    const magnitude = 10 ** Math.floor(Math.log10(raw));
    const step = STEPS.find((candidate) => candidate * magnitude >= raw);
    return (step ?? 10) * magnitude;
}

export function axis(values, intervals = 2) {
    const highest = Math.max(1, ...values);
    const step = niceStep(highest / intervals);
    const max = step * Math.ceil(highest / step);
    const ticks = [];
    for (let tick = 0; tick <= max; tick += step) ticks.push(tick);
    return { max, ticks };
}

export function percent(value, total) {
    return total > 0 ? (value / total) * 100 : 0;
}

export function onTimeRate({ onTime, late }) {
    const total = onTime + late;
    return total > 0 ? Math.round((onTime / total) * 100) : null;
}

export function busiest(days) {
    return days.reduce((best, day) => (day.count > (best?.count ?? 0) ? day : best), null);
}
