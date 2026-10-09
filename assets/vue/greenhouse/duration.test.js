import { describe, expect, it } from 'vitest';
import { duration } from './duration.js';

describe('duration', () => {
    it('rounds up to the next minute, never showing zero', () => {
        expect(duration(1)).toEqual({ unit: 'minutes', days: 0, hours: 0, minutes: 1 });
        expect(duration(0)).toEqual({ unit: 'minutes', days: 0, hours: 0, minutes: 1 });
        expect(duration(61)).toEqual({ unit: 'minutes', days: 0, hours: 0, minutes: 2 });
        expect(duration(59 * 60)).toEqual({ unit: 'minutes', days: 0, hours: 0, minutes: 59 });
    });

    it('speaks in hours and minutes under a day', () => {
        expect(duration(3600)).toEqual({ unit: 'hours', days: 0, hours: 1, minutes: 0 });
        expect(duration(11 * 3600 + 49 * 60 + 1)).toEqual({ unit: 'hours', days: 0, hours: 11, minutes: 50 });
        expect(duration(23 * 3600 + 59 * 60 + 30)).toEqual({ unit: 'days', days: 1, hours: 0, minutes: 0 });
    });

    it('speaks in days and hours beyond', () => {
        expect(duration(90_000)).toEqual({ unit: 'days', days: 1, hours: 1, minutes: 0 });
        expect(duration(3 * 86_400 + 5 * 3600 + 120)).toEqual({ unit: 'days', days: 3, hours: 5, minutes: 2 });
    });
});
