import { describe, expect, it } from 'vitest';
import { INBOX, formatSelection, matchesSelection, mergeOrder, parseSelection } from './projectFilter.js';

describe('project filter', () => {
    it('reads and writes the selection as a comma separated list', () => {
        expect(parseSelection('a,inbox,a')).toEqual(['a', INBOX]);
        expect(parseSelection('')).toEqual([]);
        expect(parseSelection(undefined)).toEqual([]);
        expect(parseSelection(['a'])).toEqual([]);
        expect(formatSelection(['a', INBOX])).toBe('a,inbox');
        expect(formatSelection([])).toBeUndefined();
    });

    it('keeps every task when nothing is selected', () => {
        expect(matchesSelection({ projectId: 'a' }, [])).toBe(true);
        expect(matchesSelection({ projectId: null }, [])).toBe(true);
    });

    it('keeps tasks of the selected projects, the inbox standing for tasks without a project', () => {
        expect(matchesSelection({ projectId: 'a' }, ['a'])).toBe(true);
        expect(matchesSelection({ projectId: 'b' }, ['a'])).toBe(false);
        expect(matchesSelection({ projectId: null }, ['a'])).toBe(false);
        expect(matchesSelection({ projectId: null }, ['a', INBOX])).toBe(true);
    });
});

describe('mergeOrder', () => {
    it('is the visible order when nothing is hidden', () => {
        expect(mergeOrder(['a', 'b', 'c'], ['c', 'a', 'b'])).toEqual(['c', 'a', 'b']);
    });

    it('keeps hidden tasks where they were and reorders the visible ones around them', () => {
        expect(mergeOrder(['a', 'h1', 'b', 'h2', 'c'], ['c', 'b', 'a'])).toEqual(['c', 'h1', 'b', 'h2', 'a']);
    });

    it('slots a task dropped in from another quadrant at its visible position', () => {
        expect(mergeOrder(['a', 'h', 'b'], ['x', 'a', 'b'])).toEqual(['x', 'h', 'a', 'b']);
        expect(mergeOrder(['a', 'h', 'b'], ['a', 'b', 'x'])).toEqual(['a', 'h', 'b', 'x']);
        expect(mergeOrder([], ['x'])).toEqual(['x']);
    });
});
