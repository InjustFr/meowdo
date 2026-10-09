import { describe, expect, it } from 'vitest';
import { fillRatio, ratePerHour, secondsUntilFull, tankAt, tankMilliAt } from './tank.js';

const HOUR = 3_600_000;
const view = (overrides = {}) => ({ tankMilli: 0, capacity: 100, rateMilliPerHour: 4000, ...overrides });

describe('tank', () => {
    it('fills from elapsed whole seconds, in thousandths of dew', () => {
        expect(tankMilliAt(view(), 0)).toBe(0);
        expect(tankMilliAt(view(), 999)).toBe(0);
        expect(tankMilliAt(view(), 1000)).toBe(1);
        expect(tankMilliAt(view({ tankMilli: 500 }), HOUR)).toBe(4500);
        expect(tankMilliAt(view({ rateMilliPerHour: 2200 }), 1800 * 1000)).toBe(1100);
    });

    it('never goes past the condenser capacity', () => {
        expect(tankMilliAt(view(), 100 * HOUR)).toBe(100_000);
        expect(tankMilliAt(view({ tankMilli: 99_999 }), HOUR)).toBe(100_000);
    });

    it('ignores a clock that went backwards', () => {
        expect(tankMilliAt(view({ tankMilli: 3000 }), -HOUR)).toBe(3000);
    });

    it('shows whole dew, the fraction staying in the tank', () => {
        expect(tankAt(view({ tankMilli: 1999 }), 0)).toBe(1);
        expect(tankAt(view({ tankMilli: 41_000 }), 15 * 60 * 1000)).toBe(42);
    });

    it('stays still without production', () => {
        expect(tankAt(view({ tankMilli: 7000, rateMilliPerHour: 0 }), 10 * HOUR)).toBe(7);
    });

    it('measures how full the tank is', () => {
        expect(fillRatio(view({ tankMilli: 25_000 }), 0)).toBe(0.25);
        expect(fillRatio(view(), 100 * HOUR)).toBe(1);
    });

    it('counts the seconds until the tank is full, rounded up', () => {
        expect(secondsUntilFull(view(), 0)).toBe(90_000);
        expect(secondsUntilFull(view({ rateMilliPerHour: 7000, tankMilli: 99_999 }), 0)).toBe(1);
        expect(secondsUntilFull(view(), HOUR)).toBe(86_400);
    });

    it('is full at once when nothing is missing, and never without production', () => {
        expect(secondsUntilFull(view({ tankMilli: 100_000 }), 0)).toBe(0);
        expect(secondsUntilFull(view({ rateMilliPerHour: 0 }), 0)).toBeNull();
        expect(secondsUntilFull(view({ rateMilliPerHour: 0, tankMilli: 100_000 }), 0)).toBe(0);
    });

    it('rounds the hourly production down to a tenth of dew', () => {
        expect(ratePerHour(4000)).toBe(4);
        expect(ratePerHour(2200)).toBe(2.2);
        expect(ratePerHour(9999)).toBe(9.9);
        expect(ratePerHour(0)).toBe(0);
    });
});
