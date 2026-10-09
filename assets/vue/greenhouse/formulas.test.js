import { describe, expect, it } from 'vitest';
import { bonusBreakdown, yieldBreakdown } from './formulas.js';

describe('yieldBreakdown', () => {
    it('sums the potted mosses and skips empty pots', () => {
        const pots = [{ species: 'a', yield: 2 }, { species: null, yield: 0 }, { species: 'b', yield: 5 }];

        expect(yieldBreakdown(pots, 30)).toEqual({ terms: [2, 5], sum: 7, factor: 1.3 });
    });

    it('has no term when every pot is empty', () => {
        expect(yieldBreakdown([{ species: null, yield: 0 }], 0)).toEqual({ terms: [], sum: 0, factor: 1 });
    });
});

describe('bonusBreakdown', () => {
    it('waters Plant tasks with the whole multiplier', () => {
        expect(bonusBreakdown('schedule', 117, 3)).toEqual({ yield: 11.7, multiplier: 3, halved: false, exact: 35.1, rounded: true });
    });

    it('gives Water tasks half a watering', () => {
        expect(bonusBreakdown('do_first', 117, 3)).toEqual({ yield: 11.7, multiplier: 3, halved: true, exact: 17.55, rounded: true });
    });

    it('mists Trim tasks with the yield alone', () => {
        expect(bonusBreakdown('delegate', 120, 3)).toEqual({ yield: 12, multiplier: 1, halved: false, exact: 12, rounded: false });
    });

    it('gives Compost and unsorted tasks no bonus', () => {
        expect([bonusBreakdown('eliminate', 117, 3), bonusBreakdown(null, 117, 3)]).toEqual([null, null]);
    });
});
