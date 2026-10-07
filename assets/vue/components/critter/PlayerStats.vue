<script setup>
import { Coins, Droplets } from '@lucide/vue';
import { useI18n } from 'vue-i18n';

defineProps({
    player: { type: Object, required: true },
});

const { t } = useI18n();
</script>

<template>
    <dl class="player-stats">
        <div class="player-stats__item player-stats__item--coins">
            <dt><Coins size="1rem" :stroke-width="1.75" aria-hidden="true" /><span class="visually-hidden">{{ t('critter.coins') }}</span></dt>
            <dd class="tabular" data-test="coins">{{ player.coins }}</dd>
        </div>
        <div :class="['player-stats__item', 'player-stats__item--streak', { 'player-stats__item--cold': player.streak === 0 }]">
            <dt><Droplets size="1rem" :stroke-width="1.75" aria-hidden="true" /><span class="visually-hidden">{{ t('critter.streak') }}</span></dt>
            <dd class="tabular">{{ t('critter.days', player.streak) }}</dd>
        </div>
    </dl>
</template>

<style scoped>
.player-stats { display: flex; flex-wrap: wrap; gap: var(--space-2); margin: 0; }
.player-stats__item { display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-3); border: 0.0625rem solid var(--color-border); border-radius: var(--radius-pill); background: var(--color-surface); color: var(--color-ink); font-weight: 600; font-size: var(--font-size-sm); }
.player-stats__item dt { display: inline-flex; }
.player-stats__item dd { margin: 0; }
.player-stats__item--coins dt { color: var(--color-warning); }
.player-stats__item--streak dt { color: var(--color-dew); }
.player-stats__item--cold dt { color: var(--color-subtle); }
</style>
