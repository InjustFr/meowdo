import { describe, expect, it } from 'vitest';
import { axis, busiest, niceStep, onTimeRate, percent } from './scale.js';

describe('scale', () => {
    it('rounds steps to 1, 2 or 5 times a power of ten', () => {
        expect(niceStep(0.5)).toBe(1);
        expect(niceStep(1.5)).toBe(2);
        expect(niceStep(3)).toBe(5);
        expect(niceStep(7)).toBe(10);
        expect(niceStep(12)).toBe(20);
    });

    it('builds an axis with clean ticks covering the highest value', () => {
        expect(axis([0, 0, 0])).toEqual({ max: 1, ticks: [0, 1] });
        expect(axis([1, 3, 2])).toEqual({ max: 4, ticks: [0, 2, 4] });
        expect(axis([4])).toEqual({ max: 4, ticks: [0, 2, 4] });
        expect(axis([9, 2])).toEqual({ max: 10, ticks: [0, 5, 10] });
        expect(axis([23])).toEqual({ max: 40, ticks: [0, 20, 40] });
    });

    it('scales a value to a percentage of a total', () => {
        expect(percent(1, 4)).toBe(25);
        expect(percent(3, 0)).toBe(0);
    });

    it('rates deadlines met, or nothing without deadlines', () => {
        expect(onTimeRate({ onTime: 3, late: 1 })).toBe(75);
        expect(onTimeRate({ onTime: 2, late: 1 })).toBe(67);
        expect(onTimeRate({ onTime: 0, late: 0 })).toBeNull();
    });

    it('finds the first busiest day, or none when nothing was done', () => {
        expect(busiest([{ date: 'a', count: 1 }, { date: 'b', count: 3 }, { date: 'c', count: 3 }])).toEqual({ date: 'b', count: 3 });
        expect(busiest([{ date: 'a', count: 0 }])).toBeNull();
    });
});
