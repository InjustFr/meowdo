import { describe, expect, it } from 'vitest';
import en from '../i18n/en/tasks.json';
import fr from '../i18n/fr/tasks.json';
import { RECURRENCE_UNITS, parseInterval, recurrenceLabel } from './recurrence.js';

describe('recurrence', () => {
    it('parses an interval between 1 and 365', () => {
        expect(parseInterval('1')).toBe(1);
        expect(parseInterval(' 12 ')).toBe(12);
        expect(parseInterval('365')).toBe(365);
        expect(parseInterval(7)).toBe(7);
    });

    it('rejects anything else', () => {
        for (const text of ['', '0', '366', '-1', '1.5', '2 weeks', 'often', null, undefined]) {
            expect(parseInterval(text)).toBeNull();
        }
    });

    it('labels a recurrence with a plural message', () => {
        const calls = [];
        const t = (key, count) => {
            calls.push([key, count]);
            return `${key}:${count}`;
        };

        expect(recurrenceLabel({ interval: 2, unit: 'week' }, t)).toBe('tasks.repeat.every.week:2');
        expect(recurrenceLabel(null, t)).toBeNull();
        expect(calls).toEqual([['tasks.repeat.every.week', 2]]);
    });

    it('has a singular and a plural label for every unit in every language', () => {
        for (const messages of [en, fr]) {
            for (const unit of RECURRENCE_UNITS) {
                const [one, many] = messages.repeat.every[unit].split(' | ');
                expect(one).not.toContain('{n}');
                expect(many).toContain('{n}');
                expect(messages.repeat.units[unit].split(' | ')).toHaveLength(2);
            }
        }
    });
});
