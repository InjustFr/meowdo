import { describe, expect, it } from 'vitest';
import { hasOpenSubtasks, nestSubtasks } from './subtasks.js';

const task = (overrides) => ({
    id: overrides.id,
    parentId: null,
    done: false,
    subtaskCount: 0,
    subtasksDone: 0,
    createdAt: '2026-10-01T10:00:00+00:00',
    ...overrides,
});

const shape = (nested) => nested.map(({ task: parent, subtasks }) => [parent.id, subtasks.map((subtask) => subtask.id)]);

describe('nestSubtasks', () => {
    it('puts subtasks under their parent when both are listed, in the order they were added', () => {
        const tasks = [
            task({ id: 'render', parentId: 'drawing', createdAt: '2026-10-01T10:03:00+00:00' }),
            task({ id: 'drawing' }),
            task({ id: 'vet' }),
            task({ id: 'sketch', parentId: 'drawing', createdAt: '2026-10-01T10:01:00+00:00' }),
        ];

        expect(shape(nestSubtasks(tasks))).toEqual([['drawing', ['sketch', 'render']], ['vet', []]]);
    });

    it('keeps a subtask at the top level when its parent is not listed', () => {
        const tasks = [task({ id: 'sketch', parentId: 'drawing' }), task({ id: 'vet' })];

        expect(shape(nestSubtasks(tasks))).toEqual([['sketch', []], ['vet', []]]);
    });

    it('keeps the order of the top level', () => {
        const tasks = [task({ id: 'b' }), task({ id: 'a' })];

        expect(shape(nestSubtasks(tasks))).toEqual([['b', []], ['a', []]]);
    });

    it('breaks creation ties by id', () => {
        const tasks = [task({ id: 'drawing' }), task({ id: 'b', parentId: 'drawing' }), task({ id: 'a', parentId: 'drawing' })];

        expect(shape(nestSubtasks(tasks))).toEqual([['drawing', ['a', 'b']]]);
    });
});

describe('hasOpenSubtasks', () => {
    it('is true for an open task with subtasks left to do', () => {
        expect(hasOpenSubtasks(task({ id: 'drawing', subtaskCount: 3, subtasksDone: 2 }))).toBe(true);
        expect(hasOpenSubtasks(task({ id: 'drawing', subtaskCount: 3, subtasksDone: 3 }))).toBe(false);
        expect(hasOpenSubtasks(task({ id: 'drawing', done: true, subtaskCount: 3, subtasksDone: 3 }))).toBe(false);
        expect(hasOpenSubtasks(task({ id: 'vet' }))).toBe(false);
    });
});
