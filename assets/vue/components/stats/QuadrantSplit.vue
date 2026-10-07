<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { percent } from '../../stats/scale.js';

const props = defineProps({
    counts: { type: Object, required: true },
});

const { t } = useI18n();

const ORDER = ['do_first', 'schedule', 'delegate', 'eliminate', 'unsorted'];

const total = computed(() => ORDER.reduce((sum, key) => sum + (props.counts[key] ?? 0), 0));
const parts = computed(() => ORDER.map((key) => ({
    key,
    count: props.counts[key] ?? 0,
    name: key === 'unsorted' ? t('matrix.unsorted') : t(`matrix.quadrants.${key}.name`),
    color: `var(--quadrant-${key.replace('_', '-')})`,
})));
</script>

<template>
    <div class="quadrant-split">
        <div v-if="total" class="quadrant-split__bar" aria-hidden="true">
            <span v-for="part in parts.filter((candidate) => candidate.count)" :key="part.key" class="quadrant-split__segment" :style="{ flexGrow: part.count, background: part.color }" />
        </div>
        <ul class="quadrant-split__legend">
            <li v-for="part in parts" :key="part.key" :class="['quadrant-split__item', { 'quadrant-split__item--none': !part.count }]">
                <span class="quadrant-split__swatch" :style="{ background: part.color }" aria-hidden="true" />
                <span class="quadrant-split__name">{{ part.name }}</span>
                <span class="quadrant-split__count tabular">{{ part.count }}</span>
                <span class="quadrant-split__share tabular">{{ Math.round(percent(part.count, total)) }}%</span>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.quadrant-split { display: flex; flex-direction: column; gap: var(--space-4); }
.quadrant-split__bar { display: flex; gap: 0.125rem; height: 0.75rem; }
.quadrant-split__segment { flex-basis: 0; min-width: 0.25rem; }
.quadrant-split__segment:first-child { border-radius: var(--radius-sm) 0 0 var(--radius-sm); }
.quadrant-split__segment:last-child { border-radius: 0 var(--radius-sm) var(--radius-sm) 0; }
.quadrant-split__segment:only-child { border-radius: var(--radius-sm); }
.quadrant-split__legend { display: flex; flex-direction: column; gap: var(--space-2); margin: 0; padding: 0; list-style: none; }
.quadrant-split__item { display: grid; grid-template-columns: auto minmax(0, 1fr) auto 3rem; align-items: center; gap: var(--space-2); font-size: var(--font-size-md); }
.quadrant-split__item--none { color: var(--color-subtle); }
.quadrant-split__swatch { width: 0.625rem; height: 0.625rem; border-radius: 50%; }
.quadrant-split__count { color: var(--color-ink); font-weight: 600; }
.quadrant-split__item--none .quadrant-split__count { color: var(--color-subtle); font-weight: 400; }
.quadrant-split__share { color: var(--color-subtle); text-align: right; }
</style>
