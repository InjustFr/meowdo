import { computed, ref } from 'vue';
import { useApi } from './useApi.js';

const player = ref(null);

export function usePlayer() {
    const api = useApi();

    const progress = computed(() => {
        if (!player.value) return 0;
        const { xp, levelStartXp, nextLevelXp } = player.value;
        return Math.min(1, Math.max(0, (xp - levelStartXp) / (nextLevelXp - levelStartXp)));
    });

    return {
        player,
        progress,
        load: () => api.load('/api/player', player),
        markAchievementsSeen: () => api.post('/api/achievements/seen'),
        buy: (slug) => api.post(`/api/shop/${slug}/buy`),
        wear: (slug) => api.post(`/api/cat/wear/${slug}`),
        takeOff: (slot) => api.post(`/api/cat/take-off/${slot}`),
        rename: (name) => api.put('/api/cat/name', { name }),
        recoat: (coat) => api.put('/api/cat/coat', { coat }),
    };
}
