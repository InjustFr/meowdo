const stamps = new WeakMap();

export function receivedAt(view, now = Date.now()) {
    if (!stamps.has(view)) stamps.set(view, now);
    return stamps.get(view);
}
