<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { Leaf, Sprout } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import PlayerStats from './PlayerStats.vue';
import SpeciesCard from './SpeciesCard.vue';
import XpBar from './XpBar.vue';
import { useCelebration } from '../../composables/useCelebration.js';
import { usePlayer } from '../../composables/usePlayer.js';
import { speciesOf } from '../../herbarium/catalog.js';

defineProps({
    compact: { type: Boolean, default: false },
});

const { t } = useI18n();
const { player, progress } = usePlayer();
const { state } = useCelebration();

const latest = computed(() => player.value?.latestSpecimen ?? null);
const thumbnail = computed(() => (latest.value ? speciesOf(latest.value.species)?.photo : null));
</script>

<template>
    <aside v-if="player" :class="['herbarium-desk', { 'herbarium-desk--compact': compact }]" :aria-label="t('herbarium.deskLabel')">
        <div class="herbarium-desk__scene">
            <img v-if="compact && thumbnail" class="herbarium-desk__thumbnail" :src="thumbnail" alt="" />
            <SpeciesCard v-else-if="latest" :slug="latest.species" :collected-at="latest.collectedAt" />
            <div v-else class="herbarium-desk__empty">
                <Sprout size="1.75rem" :stroke-width="1.5" aria-hidden="true" />
                <p v-if="!compact">{{ t('herbarium.firstHint') }}</p>
            </div>
            <TransitionGroup name="burst" tag="div" class="herbarium-desk__bursts" aria-live="polite">
                <p v-for="burst in state.bursts" :key="burst.id" class="herbarium-desk__burst">{{ t('herbarium.burstXp', { xp: burst.xp }) }}</p>
            </TransitionGroup>
        </div>
        <div class="herbarium-desk__info">
            <XpBar :player="player" :progress="progress" />
            <PlayerStats :player="player" />
            <RouterLink v-if="!compact" to="/herbarium" class="herbarium-desk__link"><Leaf size="1rem" :stroke-width="1.75" aria-hidden="true" />{{ t('herbarium.open') }}</RouterLink>
        </div>
    </aside>
</template>

<style scoped>
.herbarium-desk { display: flex; flex-direction: column; gap: var(--space-4); }
.herbarium-desk__scene { position: relative; }
.herbarium-desk__info { display: flex; flex-direction: column; gap: var(--space-4); }
.herbarium-desk__empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: var(--space-2); aspect-ratio: 4 / 3; padding: var(--space-4); border: 0.0625rem dashed var(--color-border-strong); border-radius: var(--radius); color: var(--color-subtle); font-size: var(--font-size-sm); text-align: center; }
.herbarium-desk__link { display: inline-flex; align-items: center; gap: var(--space-2); align-self: flex-start; color: var(--color-muted); font-size: var(--font-size-sm); font-weight: 500; text-decoration: none; transition: color var(--transition); }
.herbarium-desk__link:hover { color: var(--color-ink); }

.herbarium-desk__bursts { position: absolute; inset: 0; pointer-events: none; }
.herbarium-desk__burst { position: absolute; top: 18%; left: 50%; padding: var(--space-1) var(--space-3); border-radius: var(--radius); background: var(--color-surface); box-shadow: var(--shadow); color: var(--color-accent-strong); font-family: var(--font-display); font-size: 1.375rem; line-height: 1.1; transform: translateX(-50%); animation: burst-rise 1500ms ease-out forwards; }

@keyframes burst-rise {
    0%, 25% { opacity: 0; transform: translate(-50%, 0.5rem); }
    40% { opacity: 1; transform: translate(-50%, 0); }
    80% { opacity: 1; }
    100% { opacity: 0; transform: translate(-50%, -1.5rem); }
}

@media (prefers-reduced-motion: reduce) {
    .herbarium-desk__burst { animation: none; opacity: 0; }
}

.herbarium-desk--compact { flex-direction: row; align-items: center; gap: var(--space-3); }
.herbarium-desk--compact .herbarium-desk__scene { flex: 0 0 4.5rem; }
.herbarium-desk--compact .herbarium-desk__thumbnail { display: block; width: 4.5rem; height: 4.5rem; border: 0.0625rem solid var(--color-border); border-radius: var(--radius); object-fit: cover; }
.herbarium-desk--compact .herbarium-desk__empty { width: 4.5rem; height: 4.5rem; aspect-ratio: auto; padding: 0; }
.herbarium-desk--compact .herbarium-desk__info { flex: 1; gap: var(--space-2); min-width: 0; }
.herbarium-desk--compact .herbarium-desk__burst { top: 4%; padding: 0 var(--space-2); font-size: 1rem; }
</style>
