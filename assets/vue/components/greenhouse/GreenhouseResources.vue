<script setup>
import { computed } from 'vue';
import { Droplet } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import BaseButton from '../ui/BaseButton.vue';
import TankMeter from './TankMeter.vue';
import { useDew } from '../../composables/useDew.js';

const props = defineProps({
    greenhouse: { type: Object, required: true },
    tank: { type: Number, required: true },
    ratio: { type: Number, required: true },
    secondsUntilFull: { type: Number, default: null },
    collecting: { type: Boolean, default: false },
});
const emit = defineEmits(['collect']);

const { t } = useI18n();
const { number, rate, duration } = useDew();

const misters = computed(() => props.greenhouse.facilities.find((facility) => facility.id === 'misters')?.effect ?? 0);
const usedPots = computed(() => props.greenhouse.pots.filter((pot) => pot.species).length);
const full = computed(() => props.secondsUntilFull === 0);
const tankHint = computed(() => {
    if (props.secondsUntilFull === null) return t('greenhouse.resources.idle');
    return t('greenhouse.resources.fullIn', { duration: duration(props.secondsUntilFull) });
});
</script>

<template>
    <dl class="resources">
        <div class="resources__tile resources__tile--dew">
            <dt class="resources__label"><Droplet size="0.875rem" :stroke-width="2" aria-hidden="true" />{{ t('greenhouse.resources.dew') }}</dt>
            <dd class="resources__value tabular" data-test="dew-balance">{{ number(greenhouse.dew) }}</dd>
            <dd class="resources__hint">{{ t('greenhouse.resources.gathered', { gathered: number(greenhouse.dewGathered) }) }}</dd>
        </div>
        <div :class="['resources__tile', 'resources__tile--tank', { 'resources__tile--full': full }]">
            <dt class="resources__label">{{ t('greenhouse.resources.tank') }}</dt>
            <dd class="resources__tank">
                <span class="resources__value tabular" data-test="tank">{{ t('greenhouse.resources.tankLevel', { tank: number(tank), capacity: number(greenhouse.capacity) }) }}</span>
                <BaseButton :variant="tank >= 1 ? 'primary' : 'secondary'" :disabled="tank < 1" :loading="collecting" @click="emit('collect')">
                    {{ t('greenhouse.resources.collect', { n: number(tank) }, tank) }}
                </BaseButton>
            </dd>
            <dd class="resources__meter"><TankMeter :tank="tank" :capacity="greenhouse.capacity" :ratio="ratio" /></dd>
            <dd class="resources__hint">
                <span v-if="!full">{{ tankHint }}</span>
                <span aria-live="polite">{{ full ? t('greenhouse.resources.full') : '' }}</span>
            </dd>
        </div>
        <div class="resources__tile">
            <dt class="resources__label">{{ t('greenhouse.resources.production') }}</dt>
            <dd class="resources__value tabular">{{ t('greenhouse.resources.perHour', { rate: rate(greenhouse.rateMilliPerHour) }) }}</dd>
            <dd class="resources__hint">{{ misters ? t('greenhouse.resources.misters', { bonus: misters }) : t('greenhouse.resources.noMisters') }}</dd>
        </div>
        <div class="resources__tile">
            <dt class="resources__label">{{ t('greenhouse.resources.pots') }}</dt>
            <dd class="resources__value tabular">{{ t('greenhouse.resources.potsUsed', { used: usedPots, total: greenhouse.pots.length }) }}</dd>
            <dd class="resources__hint">{{ t('greenhouse.resources.potsMax', { max: greenhouse.maxPots }) }}</dd>
        </div>
    </dl>
</template>

<style scoped>
.resources { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 2fr) minmax(0, 1fr) minmax(0, 1fr); gap: var(--space-3); margin: 0; }
.resources__tile { display: flex; flex-direction: column; gap: var(--space-1); min-width: 0; padding: var(--space-3) var(--space-4); border: 0.0625rem solid var(--color-border); border-radius: var(--radius); background: var(--color-surface); }
.resources__tile dd { margin: 0; }
.resources__tile--dew { border-color: color-mix(in oklch, var(--color-dew) 40%, var(--color-border)); }
.resources__tile--full { border-color: color-mix(in oklch, var(--color-dew) 70%, var(--color-border)); background: color-mix(in oklch, var(--color-dew) 6%, var(--color-surface)); }
.resources__label { display: inline-flex; align-items: center; gap: var(--space-1); color: var(--color-muted); font-size: var(--font-size-sm); }
.resources__label svg { color: var(--color-dew); }
.resources__value { color: var(--color-ink); font-size: 1.5rem; font-weight: 600; line-height: 1.15; overflow-wrap: anywhere; }
.resources__tile--dew .resources__value { color: color-mix(in oklch, var(--color-dew) 65%, var(--color-ink)); }
.resources__tank { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--space-2); }
.resources__meter { padding-block: var(--space-1); }
.resources__hint { color: var(--color-subtle); font-size: var(--font-size-xs); }
.resources__tile--full .resources__hint { color: var(--color-ink); font-weight: 500; }

@media (max-width: 64rem) {
    .resources { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .resources__tile--tank { grid-column: 1 / -1; order: -1; }
}

@media (max-width: 30rem) {
    .resources { gap: var(--space-2); }
    .resources__tile { padding: var(--space-3); }
    .resources__value { font-size: 1.25rem; }
}
</style>
