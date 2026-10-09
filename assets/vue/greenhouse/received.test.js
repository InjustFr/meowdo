import { describe, expect, it } from 'vitest';
import { receivedAt } from './received.js';

describe('receivedAt', () => {
    it('stamps a view the first time it is seen', () => {
        expect(receivedAt({}, 1000)).toBe(1000);
    });

    it('keeps the first stamp of a cached view shown again later', () => {
        const view = { tankMilli: 0 };
        receivedAt(view, 1000);

        expect(receivedAt(view, 3_601_000)).toBe(1000);
    });
});
