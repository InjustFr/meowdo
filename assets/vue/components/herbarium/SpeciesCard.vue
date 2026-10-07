<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDates } from '../../composables/useDates.js';
import { speciesOf } from '../../herbarium/catalog.js';

const props = defineProps({
    slug: { type: String, required: true },
    collectedAt: { type: String, default: null },
    revealed: { type: Boolean, default: false },
});

const { t } = useI18n();
const dates = useDates();
const species = computed(() => speciesOf(props.slug));
const name = computed(() => t(`species.${props.slug}`));
</script>

<template>
    <figure v-if="species" :class="['species-card', { 'species-card--revealed': revealed }]" data-test="species-card">
        <img class="species-card__photo" :src="species.photo" alt="" loading="lazy" />
        <figcaption class="species-card__label">
            <strong class="species-card__name">{{ name }}</strong>
            <em v-if="name !== species.scientificName" class="species-card__latin">{{ species.scientificName }}</em>
            <span v-if="collectedAt" class="species-card__date">{{ t('herbarium.collectedOn', { date: dates.short(collectedAt.slice(0, 10)) }) }}</span>
            <a class="species-card__credit" :href="species.source" target="_blank" rel="noopener">{{ t('herbarium.photoBy', { attribution: species.attribution }) }}</a>
        </figcaption>
    </figure>
</template>

<style scoped>
.species-card { display: flex; flex-direction: column; margin: 0; overflow: hidden; border: 0.0625rem solid var(--color-border); border-radius: var(--radius); background: var(--color-surface); }
.species-card__photo { display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: cover; background: var(--color-accent-soft); }
.species-card__label { display: flex; flex-direction: column; gap: 0.125rem; padding: var(--space-3) var(--space-4) var(--space-4); border-top: 0.0625rem solid var(--color-border); }
.species-card__name { color: var(--color-ink); font-family: var(--font-display); font-size: 1.2rem; font-weight: 400; line-height: 1.15; }
.species-card__latin { color: var(--color-muted); font-size: var(--font-size-sm); }
.species-card__date { color: var(--color-subtle); font-size: var(--font-size-xs); }
.species-card__credit { margin-top: var(--space-1); overflow: hidden; color: var(--color-subtle); font-size: var(--font-size-2xs); text-decoration: none; text-overflow: ellipsis; white-space: nowrap; }
.species-card__credit:hover { color: var(--color-muted); text-decoration: underline; }

.species-card--revealed { animation: species-reveal 900ms cubic-bezier(0.2, 0.8, 0.2, 1) both; }
.species-card--revealed .species-card__photo { animation: species-develop 1400ms ease-out both; }

@keyframes species-reveal {
    0% { opacity: 0; transform: translateY(1.5rem) rotate(-3deg) scale(0.92); }
    60% { opacity: 1; transform: translateY(-0.25rem) rotate(0.5deg) scale(1.01); }
    100% { opacity: 1; transform: none; }
}

@keyframes species-develop {
    0% { filter: blur(0.75rem) saturate(0) brightness(1.3); }
    100% { filter: none; }
}

@media (prefers-reduced-motion: reduce) {
    .species-card--revealed, .species-card--revealed .species-card__photo { animation: none; }
}
</style>
