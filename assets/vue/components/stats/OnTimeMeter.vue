<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { onTimeRate } from '../../stats/scale.js';

const props = defineProps({
    onTime: { type: Object, required: true },
});

const { t } = useI18n();

const rate = computed(() => onTimeRate(props.onTime));
</script>

<template>
    <p v-if="rate === null" class="on-time__empty">{{ t('stats.onTime.empty') }}</p>
    <div v-else class="on-time">
        <p class="on-time__rate">{{ t('stats.onTime.rate', { rate }) }}</p>
        <div class="on-time__track" role="meter" :aria-valuenow="rate" aria-valuemin="0" aria-valuemax="100" :aria-label="t('stats.onTime.title')">
            <span class="on-time__fill" :style="{ width: `${rate}%` }" />
        </div>
        <p class="on-time__detail">{{ t('stats.onTime.detail', { onTime: onTime.onTime, late: onTime.late }) }}</p>
    </div>
</template>

<style scoped>
.on-time { display: flex; flex-direction: column; gap: var(--space-2); }
.on-time__rate { color: var(--color-ink); font-size: 1.75rem; font-weight: 600; line-height: 1.1; }
.on-time__track { height: 0.5rem; overflow: hidden; border-radius: var(--radius-sm); background: var(--color-accent-soft); }
.on-time__fill { display: block; height: 100%; border-radius: var(--radius-sm); background: var(--color-accent); }
.on-time__detail, .on-time__empty { color: var(--color-muted); font-size: var(--font-size-md); }
</style>
