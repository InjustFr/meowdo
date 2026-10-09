import { afterEach, describe, expect, it } from 'vitest';
import { useCelebration } from './useCelebration.js';

describe('useCelebration', () => {
    const celebration = useCelebration();

    afterEach(() => {
        celebration.releaseAchievements();
        celebration.dismissAchievements();
    });

    it('shows new achievements at once', () => {
        celebration.unlocked(['field_trip']);

        expect(celebration.achievementsDue.value).toBe(true);
    });

    it('holds new achievements until the reveal is over', () => {
        celebration.holdAchievements();
        celebration.unlocked(['field_trip']);

        expect(celebration.achievementsDue.value).toBe(false);

        celebration.releaseAchievements();

        expect(celebration.achievementsDue.value).toBe(true);
        expect(celebration.state.achievements).toEqual(['field_trip']);
    });
});
