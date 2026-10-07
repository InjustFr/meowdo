<script setup>
import { RouterLink } from 'vue-router';
import { Droplets, Leaf } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import XpBar from './XpBar.vue';
import { useCelebration } from '../../composables/useCelebration.js';
import { usePlayer } from '../../composables/usePlayer.js';

defineProps({
    compact: { type: Boolean, default: false },
});

const { t } = useI18n();
const { player, progress } = usePlayer();
const { state } = useCelebration();
</script>

<template>
    <section v-if="player" :class="['player-progress', { 'player-progress--compact': compact }]" :aria-label="t('herbarium.progressLabel')">
        <div class="player-progress__bar">
            <XpBar :player="player" :progress="progress" />
            <TransitionGroup tag="div" class="player-progress__bursts" aria-live="polite">
                <p v-for="burst in state.bursts" :key="burst.id" class="player-progress__burst">{{ t('herbarium.burstXp', { xp: burst.xp }) }}</p>
            </TransitionGroup>
        </div>
        <div class="player-progress__links">
            <RouterLink to="/herbarium" class="player-progress__herbarium" data-test="species">
                <Leaf size="1rem" :stroke-width="1.75" aria-hidden="true" />{{ t('herbarium.collected', { collected: player.speciesCollected, total: player.speciesTotal }) }}
            </RouterLink>
            <span :class="['player-progress__streak', { 'player-progress__streak--cold': player.streak === 0 }]">
                <Droplets size="1rem" :stroke-width="1.75" aria-hidden="true" />
                <span class="visually-hidden">{{ t('herbarium.streak') }}</span>{{ t('herbarium.days', player.streak) }}
            </span>
        </div>
    </section>
</template>

<style scoped>
.player-progress { display: flex; flex-direction: column; gap: var(--space-3); padding: 0 var(--space-3); }
.player-progress__bar { position: relative; }
.player-progress__links { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--space-2); font-size: var(--font-size-sm); }

.player-progress__herbarium { display: inline-flex; align-items: center; gap: var(--space-2); color: var(--color-ink); font-weight: 600; text-decoration: none; }
.player-progress__herbarium svg { color: var(--color-accent); }
.player-progress__herbarium:hover { text-decoration: underline; text-underline-offset: 0.2em; }
.player-progress__herbarium.router-link-active { color: var(--color-accent-strong); }

.player-progress__streak { display: inline-flex; align-items: center; gap: var(--space-1); color: var(--color-muted); font-weight: 500; }
.player-progress__streak svg { color: var(--color-dew); }
.player-progress__streak--cold svg { color: var(--color-subtle); }

.player-progress__bursts { position: absolute; inset: 0; pointer-events: none; }
.player-progress__burst { position: absolute; right: 0; bottom: 100%; color: var(--color-accent-strong); font-family: var(--font-display); font-size: 1.25rem; line-height: 1; animation: burst-rise 1500ms ease-out forwards; }

@keyframes burst-rise {
    0% { opacity: 0; transform: translateY(0.75rem); }
    20% { opacity: 1; transform: translateY(0); }
    75% { opacity: 1; }
    100% { opacity: 0; transform: translateY(-1rem); }
}

@media (prefers-reduced-motion: reduce) {
    .player-progress__burst { animation: none; opacity: 0; }
}

.player-progress--compact { flex-direction: row; align-items: center; gap: var(--space-4); padding: 0; }
.player-progress--compact .player-progress__bar { flex: 1; min-width: 0; }
.player-progress--compact .player-progress__links { flex-direction: column; align-items: flex-end; gap: var(--space-1); }
</style>
