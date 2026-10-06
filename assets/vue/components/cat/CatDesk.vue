<script setup>
import { computed, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';
import { Shirt } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import CatSvg from './CatSvg.vue';
import PlayerStats from './PlayerStats.vue';
import XpBar from './XpBar.vue';
import { useCelebration } from '../../composables/useCelebration.js';
import { usePlayer } from '../../composables/usePlayer.js';

defineProps({
    compact: { type: Boolean, default: false },
});

const { t } = useI18n();
const { player, progress } = usePlayer();
const { state } = useCelebration();

const slapping = ref(false);
let timer = null;
watch(() => state.slaps, () => {
    slapping.value = false;
    window.clearTimeout(timer);
    requestAnimationFrame(() => {
        slapping.value = true;
        timer = window.setTimeout(() => { slapping.value = false; }, 1100);
    });
});

const cat = computed(() => player.value?.cat);
</script>

<template>
    <aside v-if="player" :class="['cat-desk', { 'cat-desk--compact': compact }]" :aria-label="t('cat.deskLabel', { name: cat.name })">
        <div class="cat-desk__scene">
            <CatSvg :coat="cat.coat" :mood="cat.mood" :outfit="cat.outfit" :slapping="slapping" :label="t(`cat.mood.${cat.mood}`, { name: cat.name })" />
            <TransitionGroup name="burst" tag="div" class="cat-desk__bursts" aria-live="polite">
                <p v-for="burst in state.bursts" :key="burst.id" class="cat-desk__burst">
                    <span>{{ t('cat.burstXp', { xp: burst.xp }) }}</span>
                    <span class="cat-desk__burst-coins">{{ t('cat.burstCoins', { coins: burst.coins }) }}</span>
                </p>
            </TransitionGroup>
        </div>
        <div class="cat-desk__info">
            <p class="cat-desk__name">{{ cat.name }}</p>
            <p class="cat-desk__mood">{{ t(`cat.mood.${cat.mood}`, { name: cat.name }) }}</p>
            <XpBar :player="player" :progress="progress" />
            <PlayerStats :player="player" />
            <RouterLink v-if="!compact" to="/shop" class="cat-desk__shop"><Shirt size="1rem" aria-hidden="true" />{{ t('cat.dressUp', { name: cat.name }) }}</RouterLink>
        </div>
    </aside>
</template>

<style scoped>
.cat-desk { display: flex; flex-direction: column; gap: var(--space-4); }
.cat-desk__scene { position: relative; border-radius: 1.125rem; box-shadow: var(--shadow); }
.cat-desk__info { display: flex; flex-direction: column; gap: var(--space-3); }
.cat-desk__name { font-family: var(--font-display); font-size: 1.75rem; line-height: 1; }
.cat-desk__mood { margin-top: calc(-1 * var(--space-2)); color: var(--color-muted); font-size: var(--font-size-sm); }
.cat-desk__shop { display: inline-flex; align-items: center; gap: var(--space-2); align-self: flex-start; color: var(--color-muted); font-size: var(--font-size-sm); font-weight: 700; text-decoration: none; }
.cat-desk__shop:hover { color: var(--color-accent); }

.cat-desk__bursts { position: absolute; inset: 0; pointer-events: none; }
.cat-desk__burst { position: absolute; top: 28%; left: 50%; display: flex; flex-direction: column; align-items: center; transform: translateX(-50%); color: var(--lamp); font-family: var(--font-display); font-size: 1.75rem; line-height: 1.05; text-shadow: 0 0.125rem 0 var(--night); animation: burst-rise 1500ms ease-out forwards; }
.cat-desk__burst-coins { font-size: 1.125rem; color: var(--moonmilk); }

@keyframes burst-rise {
    0% { opacity: 0; transform: translate(-50%, 0.5rem) scale(0.8); }
    15% { opacity: 1; transform: translate(-50%, 0) scale(1.08); }
    70% { opacity: 1; }
    100% { opacity: 0; transform: translate(-50%, -2.5rem) scale(1); }
}

.cat-desk--compact { flex-direction: row; align-items: center; gap: var(--space-3); }
.cat-desk--compact .cat-desk__scene { flex: 0 0 6.5rem; border-radius: 0.75rem; }
.cat-desk--compact .cat-desk__info { flex: 1; gap: var(--space-2); min-width: 0; }
.cat-desk--compact .cat-desk__name { font-size: 1.25rem; }
.cat-desk--compact .cat-desk__mood { display: none; }
.cat-desk--compact .cat-desk__burst { top: 10%; font-size: 1.125rem; }
.cat-desk--compact .cat-desk__burst-coins { font-size: 0.875rem; }
</style>
