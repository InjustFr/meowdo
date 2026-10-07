<script setup>
import { computed, ref } from 'vue';
import { Sprout } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import SpeciesCard from '../components/herbarium/SpeciesCard.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import PageSection from '../components/ui/PageSection.vue';
import { useApi } from '../composables/useApi.js';

const { t } = useI18n();
const herbarium = ref(null);
useApi().load('/api/herbarium', herbarium);

const specimens = computed(() => [...(herbarium.value?.specimens ?? [])].reverse());
const left = computed(() => (herbarium.value ? Math.max(0, herbarium.value.total - herbarium.value.specimens.length) : 0));
</script>

<template>
    <div class="page page--wide">
        <PageHeader :title="t('herbarium.title')" :subtitle="herbarium ? t('herbarium.subtitle', { collected: herbarium.specimens.length, total: herbarium.total }) : null" />
        <ul v-if="specimens.length" class="herbarium">
            <li v-for="specimen in specimens" :key="specimen.species">
                <SpeciesCard :slug="specimen.species" :collected-at="specimen.collectedAt" />
            </li>
        </ul>
        <PageSection v-if="left" :title="t('herbarium.left', left)">
            <ul class="herbarium__slots" aria-hidden="true">
                <li v-for="slot in left" :key="slot" class="herbarium__slot"><Sprout size="1rem" :stroke-width="1.5" /></li>
            </ul>
        </PageSection>
    </div>
</template>

<style scoped>
.herbarium { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(13rem, 100%), 1fr)); gap: var(--space-4); margin: 0; padding: 0; list-style: none; }
.herbarium__slots { display: flex; flex-wrap: wrap; gap: var(--space-2); margin: 0; padding: 0; list-style: none; }
.herbarium__slot { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border: 0.0625rem dashed var(--color-border-strong); border-radius: var(--radius); color: var(--color-subtle); }
</style>
