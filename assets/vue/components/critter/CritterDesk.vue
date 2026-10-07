<script setup>
import { computed, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';
import { Shirt } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import CritterSvg from './CritterSvg.vue';
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

const cheering = ref(false);
let timer = null;
watch(() => state.drops, () => {
    cheering.value = false;
    window.clearTimeout(timer);
    requestAnimationFrame(() => {
        cheering.value = true;
        timer = window.setTimeout(() => { cheering.value = false; }, 1600);
    });
});

const critter = computed(() => player.value?.critter);
</script>

<template>
    <aside v-if="player" :class="['critter-desk', { 'critter-desk--compact': compact }]" :aria-label="t('critter.deskLabel', { name: critter.name })">
        <div class="critter-desk__scene">
            <CritterSvg :tint="critter.tint" :mood="critter.mood" :outfit="critter.outfit" :cheering="cheering" :label="t(`critter.mood.${critter.mood}`, { name: critter.name })" />
            <TransitionGroup name="burst" tag="div" class="critter-desk__bursts" aria-live="polite">
                <p v-for="burst in state.bursts" :key="burst.id" class="critter-desk__burst">
                    <span>{{ t('critter.burstXp', { xp: burst.xp }) }}</span>
                    <span class="critter-desk__burst-coins">{{ t('critter.burstCoins', { coins: burst.coins }) }}</span>
                </p>
            </TransitionGroup>
        </div>
        <div class="critter-desk__info">
            <div class="critter-desk__names">
                <p class="critter-desk__name">{{ critter.name }}</p>
                <p class="critter-desk__mood">{{ t(`critter.mood.${critter.mood}`, { name: critter.name }) }}</p>
            </div>
            <XpBar :player="player" :progress="progress" />
            <PlayerStats :player="player" />
            <RouterLink v-if="!compact" to="/shop" class="critter-desk__shop"><Shirt size="1rem" :stroke-width="1.75" aria-hidden="true" />{{ t('critter.dressUp', { name: critter.name }) }}</RouterLink>
        </div>
    </aside>
</template>

<style scoped>
.critter-desk { display: flex; flex-direction: column; gap: var(--space-4); }
.critter-desk__scene { position: relative; overflow: hidden; border: 0.0625rem solid var(--color-border); border-radius: var(--radius); background: var(--color-surface); }
.critter-desk__info { display: flex; flex-direction: column; gap: var(--space-4); }
.critter-desk__names { display: flex; flex-direction: column; gap: var(--space-1); }
.critter-desk__name { color: var(--color-ink); font-family: var(--font-display); font-size: 1.6rem; line-height: 1; }
.critter-desk__mood { color: var(--color-muted); font-size: var(--font-size-sm); }
.critter-desk__shop { display: inline-flex; align-items: center; gap: var(--space-2); align-self: flex-start; color: var(--color-muted); font-size: var(--font-size-sm); font-weight: 500; text-decoration: none; transition: color var(--transition); }
.critter-desk__shop:hover { color: var(--color-ink); }

.critter-desk__bursts { position: absolute; inset: 0; pointer-events: none; }
.critter-desk__burst { position: absolute; top: 18%; left: 50%; display: flex; flex-direction: column; align-items: center; padding: var(--space-1) var(--space-3); border-radius: var(--radius); background: var(--color-surface); box-shadow: var(--shadow); color: var(--color-accent-strong); font-family: var(--font-display); font-size: 1.375rem; line-height: 1.1; transform: translateX(-50%); animation: burst-rise 1500ms ease-out forwards; }
.critter-desk__burst-coins { color: var(--color-muted); font-family: var(--font-body); font-size: var(--font-size-sm); font-weight: 600; }

@keyframes burst-rise {
    0%, 25% { opacity: 0; transform: translate(-50%, 0.5rem); }
    40% { opacity: 1; transform: translate(-50%, 0); }
    80% { opacity: 1; }
    100% { opacity: 0; transform: translate(-50%, -1.5rem); }
}

.critter-desk--compact { flex-direction: row; align-items: center; gap: var(--space-3); }
.critter-desk--compact .critter-desk__scene { flex: 0 0 6.5rem; }
.critter-desk--compact .critter-desk__info { flex: 1; gap: var(--space-2); min-width: 0; }
.critter-desk--compact .critter-desk__name { font-size: 1.25rem; }
.critter-desk--compact .critter-desk__mood { display: none; }
.critter-desk--compact .critter-desk__burst { top: 4%; padding: 0 var(--space-2); font-size: 1rem; }
.critter-desk--compact .critter-desk__burst-coins { display: none; }
</style>
