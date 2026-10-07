<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import RarityMark from '../components/herbarium/RarityMark.vue';
import SpeciesCard from '../components/herbarium/SpeciesCard.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import PageSection from '../components/ui/PageSection.vue';
import { useApi } from '../composables/useApi.js';

const RARITIES = ['common', 'uncommon', 'rare', 'very_rare'];

const { t } = useI18n();
const herbarium = ref(null);
useApi().load('/api/herbarium', herbarium);

const specimens = computed(() => [...(herbarium.value?.specimens ?? [])].reverse());
const left = computed(() => (herbarium.value ? Object.values(herbarium.value.remaining).reduce((sum, count) => sum + count, 0) : 0));
const remaining = computed(() => RARITIES.map((rarity) => ({ rarity, count: herbarium.value?.remaining[rarity] ?? 0 })).filter((group) => group.count > 0));
</script>

<template>
    <div class="page page--wide">
        <PageHeader :title="t('herbarium.title')" :subtitle="herbarium ? t('herbarium.subtitle', { collected: herbarium.specimens.length, total: herbarium.total }) : null" />

        <p v-if="herbarium && !specimens.length" class="herbarium__empty">{{ t('herbarium.empty') }}</p>
        <ul v-if="specimens.length" class="herbarium">
            <li v-for="specimen in specimens" :key="specimen.species">
                <SpeciesCard :slug="specimen.species" :rarity="specimen.rarity" :number="specimen.number" :collected-at="specimen.collectedAt" />
            </li>
        </ul>

        <PageSection v-if="left" :title="t('herbarium.left', left)">
            <ul class="herbarium__remaining">
                <li v-for="group in remaining" :key="group.rarity" :class="['herbarium__group', `rarity--${group.rarity}`]">
                    <span class="herbarium__group-label">
                        <RarityMark :rarity="group.rarity" />
                        <span class="herbarium__group-count tabular">{{ group.count }}</span>
                    </span>
                    <span class="herbarium__slots" aria-hidden="true">
                        <span v-for="slot in group.count" :key="slot" class="herbarium__slot" />
                    </span>
                </li>
            </ul>
            <p class="herbarium__note">{{ t('herbarium.rarityNote') }}</p>
        </PageSection>
    </div>
</template>

<style scoped>
.herbarium { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(15rem, 100%), 1fr)); gap: var(--space-4); margin: 0; padding: 0; list-style: none; }
.herbarium__empty { max-width: 40rem; color: var(--color-muted); }

.herbarium__remaining { display: flex; flex-direction: column; gap: var(--space-3); margin: 0; padding: 0; list-style: none; }
.herbarium__group { display: grid; grid-template-columns: 10rem minmax(0, 1fr); align-items: center; gap: var(--space-4); }
.herbarium__group-label { display: flex; align-items: center; justify-content: space-between; gap: var(--space-2); }
.herbarium__group-count { color: var(--color-muted); font-size: var(--font-size-sm); }
.herbarium__slots { display: flex; flex-wrap: wrap; gap: 0.375rem; }
.herbarium__slot { width: 1.25rem; height: 1.25rem; border: 0.0625rem dashed color-mix(in oklch, var(--rarity) 70%, var(--color-border)); border-radius: var(--radius-sm); background: color-mix(in oklch, var(--rarity) 8%, transparent); }
.herbarium__note { max-width: 40rem; margin-top: var(--space-2); color: var(--color-subtle); font-size: var(--font-size-sm); }

@media (max-width: 48rem) {
    .herbarium__group { grid-template-columns: minmax(0, 1fr); gap: var(--space-2); }
}
</style>
