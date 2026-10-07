import { reactive } from 'vue';
import { useSound } from './useSound.js';

const state = reactive({ drops: 0, bursts: [], levelUp: null, newSpecies: [], achievements: [] });
let nextId = 1;

export function useCelebration() {
    const { drip } = useSound();

    function reward({ reward, leveledUpTo, newSpecies }) {
        state.drops += 1;
        drip();
        if (reward) {
            const burst = { id: nextId++, xp: reward.xp };
            state.bursts.push(burst);
            window.setTimeout(() => {
                const index = state.bursts.indexOf(burst);
                if (index !== -1) state.bursts.splice(index, 1);
            }, 1600);
        }
        if (leveledUpTo) {
            state.levelUp = leveledUpTo;
            state.newSpecies = newSpecies ?? [];
        }
    }

    function unlocked(ids) {
        ids.filter((id) => !state.achievements.includes(id)).forEach((id) => state.achievements.push(id));
    }

    return {
        state,
        reward,
        unlocked,
        dismissLevelUp: () => {
            state.levelUp = null;
            state.newSpecies = [];
        },
        dismissAchievements: () => { state.achievements.splice(0); },
    };
}
