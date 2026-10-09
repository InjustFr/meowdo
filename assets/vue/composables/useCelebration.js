import { computed, reactive } from 'vue';
import { useSound } from './useSound.js';

const state = reactive({ drops: 0, bursts: [], levelUp: null, newSpecies: [], achievements: [], revealing: false });
const achievementsDue = computed(() => state.levelUp === null && !state.revealing && state.achievements.length > 0);
let nextId = 1;

export function useCelebration() {
    const { drip } = useSound();

    function reward({ reward, dew, leveledUpTo, newSpecies }) {
        state.drops += 1;
        drip();
        if (reward) {
            const burst = {
                id: nextId++,
                xp: reward.xp,
                dew: dew?.amount ?? 0,
                watered: (dew?.watering ?? 0) > 0,
                misted: (dew?.mist ?? 0) > 0,
            };
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
        achievementsDue,
        reward,
        unlocked,
        holdAchievements: () => { state.revealing = true; },
        releaseAchievements: () => { state.revealing = false; },
        dismissLevelUp: () => {
            state.levelUp = null;
            state.newSpecies = [];
        },
        dismissAchievements: () => { state.achievements.splice(0); },
    };
}
