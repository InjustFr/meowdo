import { reactive } from 'vue';
import { useSound } from './useSound.js';

const state = reactive({ slaps: 0, bursts: [], levelUp: null, achievements: [] });
let nextId = 1;

export function useCelebration() {
    const { bongo } = useSound();

    function reward({ reward, leveledUpTo }) {
        state.slaps += 1;
        bongo();
        if (reward) {
            const burst = { id: nextId++, xp: reward.xp, coins: reward.coins };
            state.bursts.push(burst);
            window.setTimeout(() => {
                const index = state.bursts.indexOf(burst);
                if (index !== -1) state.bursts.splice(index, 1);
            }, 1600);
        }
        if (leveledUpTo) state.levelUp = leveledUpTo;
    }

    function unlocked(ids) {
        ids.filter((id) => !state.achievements.includes(id)).forEach((id) => state.achievements.push(id));
    }

    return {
        state,
        reward,
        unlocked,
        dismissLevelUp: () => { state.levelUp = null; },
        dismissAchievements: () => { state.achievements.splice(0); },
    };
}
