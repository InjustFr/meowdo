import { describe, expect, it } from 'vitest';
import { compareTasks, sortTasks } from './compareTasks.js';

const task = (overrides) => ({
    id: overrides.id,
    done: false,
    quadrant: null,
    rank: 0,
    dueOn: null,
    createdAt: '2026-10-01T10:00:00+00:00',
    ...overrides,
});

describe('compareTasks', () => {
    it('puts open tasks before done ones', () => {
        expect(sortTasks([task({ id: 'b', done: true }), task({ id: 'a' })]).map((t) => t.id)).toEqual(['a', 'b']);
    });

    it('orders by quadrant: pounce, stalk, swat away, nap on it, unsorted', () => {
        const tasks = [
            task({ id: 'unsorted' }),
            task({ id: 'eliminate', quadrant: 'eliminate' }),
            task({ id: 'delegate', quadrant: 'delegate' }),
            task({ id: 'schedule', quadrant: 'schedule' }),
            task({ id: 'do_first', quadrant: 'do_first' }),
        ];
        expect(sortTasks(tasks).map((t) => t.id)).toEqual(['do_first', 'schedule', 'delegate', 'eliminate', 'unsorted']);
    });

    it('orders by rank inside a quadrant', () => {
        expect(sortTasks([task({ id: 'b', quadrant: 'schedule', rank: 2 }), task({ id: 'a', quadrant: 'schedule', rank: 1 })]).map((t) => t.id)).toEqual(['a', 'b']);
    });

    it('then by deadline with tasks without one last', () => {
        const tasks = [task({ id: 'none' }), task({ id: 'late', dueOn: '2026-10-09' }), task({ id: 'soon', dueOn: '2026-10-07' })];
        expect(sortTasks(tasks).map((t) => t.id)).toEqual(['soon', 'late', 'none']);
    });

    it('then by creation', () => {
        const tasks = [task({ id: 'new', createdAt: '2026-10-02T00:00:00+00:00' }), task({ id: 'old', createdAt: '2026-10-01T00:00:00+00:00' })];
        expect(sortTasks(tasks).map((t) => t.id)).toEqual(['old', 'new']);
    });

    it('is a consistent comparator', () => {
        const a = task({ id: 'a', quadrant: 'do_first' });
        const b = task({ id: 'b' });
        expect(Math.sign(compareTasks(a, b))).toBe(-Math.sign(compareTasks(b, a)));
    });
});
