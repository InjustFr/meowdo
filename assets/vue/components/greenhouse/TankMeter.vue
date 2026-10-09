<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDew } from '../../composables/useDew.js';

const props = defineProps({
    tank: { type: Number, required: true },
    capacity: { type: Number, required: true },
    ratio: { type: Number, required: true },
});

const { t } = useI18n();
const { number } = useDew();
const full = computed(() => props.ratio >= 1);
</script>

<template>
    <div :class="['tank-meter', { 'tank-meter--full': full }]">
        <div
            class="tank-meter__track"
            role="meter"
            :aria-label="t('greenhouse.resources.tank')"
            :aria-valuenow="tank"
            aria-valuemin="0"
            :aria-valuemax="capacity"
            :aria-valuetext="t('greenhouse.resources.tankLevel', { tank: number(tank), capacity: number(capacity) })"
        >
            <span class="tank-meter__fill" :style="{ width: `${Math.min(1, ratio) * 100}%` }" />
        </div>
    </div>
</template>

<style scoped>
.tank-meter { --tank: var(--color-dew); }
.tank-meter__track { height: 0.625rem; overflow: hidden; border-radius: var(--radius-sm); background: color-mix(in oklch, var(--tank) 14%, var(--color-surface)); box-shadow: inset 0 0 0 0.0625rem color-mix(in oklch, var(--tank) 25%, transparent); }
.tank-meter__fill { display: block; height: 100%; border-radius: var(--radius-sm); background: var(--tank); transition: width 900ms linear; }
.tank-meter--full .tank-meter__fill { background: color-mix(in oklch, var(--tank) 80%, var(--color-ink)); }
</style>
