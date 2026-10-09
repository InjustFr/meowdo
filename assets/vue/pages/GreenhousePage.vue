<script setup>
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DewSources from '../components/greenhouse/DewSources.vue';
import ExpeditionPanel from '../components/greenhouse/ExpeditionPanel.vue';
import FacilityTable from '../components/greenhouse/FacilityTable.vue';
import GreenhouseResources from '../components/greenhouse/GreenhouseResources.vue';
import PlantDialog from '../components/greenhouse/PlantDialog.vue';
import PotTable from '../components/greenhouse/PotTable.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import PageSection from '../components/ui/PageSection.vue';
import { useApi } from '../composables/useApi.js';
import { useCelebration } from '../composables/useCelebration.js';
import { useDew } from '../composables/useDew.js';
import { useNow } from '../composables/useNow.js';
import { useToast } from '../composables/useToast.js';
import { receivedAt } from '../greenhouse/received.js';
import { fillRatio, secondsUntilFull, tankAt } from '../greenhouse/tank.js';

const { t } = useI18n();
const api = useApi();
const toast = useToast();
const { rate } = useDew();
const now = useNow();
const { holdAchievements, releaseAchievements } = useCelebration();

const greenhouse = ref(null);
api.load('/api/greenhouse', greenhouse);

const viewReceivedAt = ref(Date.now());
watch(greenhouse, (view) => {
    if (view) viewReceivedAt.value = receivedAt(view);
}, { immediate: true });

const elapsed = computed(() => Math.max(0, now.value - viewReceivedAt.value));
const tank = computed(() => (greenhouse.value ? tankAt(greenhouse.value, elapsed.value) : 0));
const ratio = computed(() => (greenhouse.value ? fillRatio(greenhouse.value, elapsed.value) : 0));
const untilFull = computed(() => (greenhouse.value ? secondsUntilFull(greenhouse.value, elapsed.value) : null));

const glasshouse = computed(() => greenhouse.value?.facilities.find((facility) => facility.id === 'glasshouse')?.level ?? 1);
const subtitle = computed(() => (greenhouse.value
    ? t('greenhouse.subtitle', {
        level: glasshouse.value,
        pots: t('greenhouse.potCount', greenhouse.value.pots.length),
        rate: rate(greenhouse.value.rateMilliPerHour),
    })
    : null));

const busy = ref(false);
const collecting = ref(false);
const plantingPot = ref(null);
const plantOpen = ref(false);
const potTable = ref(null);
const found = ref(null);

async function act(action, success = null) {
    busy.value = true;
    try {
        const data = await action();
        if (success) toast.success(success(data));
        return { data };
    } catch (error) {
        toast.error(error.message);
        return null;
    } finally {
        busy.value = false;
    }
}

async function collect() {
    collecting.value = true;
    await act(() => api.post('/api/greenhouse/collect'), (data) => t('greenhouse.collected', { n: data.collected }, data.collected));
    collecting.value = false;
}

function choosePot(pot) {
    plantingPot.value = pot;
    plantOpen.value = true;
}

async function plant(species) {
    const pot = plantingPot.value;
    const planted = await act(() => api.put(`/api/greenhouse/pots/${pot}`, { species }), () => t('greenhouse.plant.done', { name: t(`species.${species}`), pot }));
    if (planted) plantOpen.value = false;
}

async function unplant(pot) {
    await act(() => api.del(`/api/greenhouse/pots/${pot}`), () => t('greenhouse.pots.unplanted', { pot }));
    await nextTick();
    potTable.value?.focusPot(pot);
}

function returnToPot(event) {
    event.preventDefault();
    const pot = plantingPot.value;
    window.setTimeout(() => potTable.value?.focusPot(pot), 0);
}

function upgrade(id) {
    const facility = greenhouse.value.facilities.find((candidate) => candidate.id === id);
    act(() => api.post(`/api/greenhouse/facilities/${id}/upgrade`), () => t('greenhouse.facilities.upgraded', { name: t(`greenhouse.facilities.${id}.name`), level: facility.level + 1 }));
}

async function launch() {
    holdAchievements();
    const expedition = await act(() => api.post('/api/greenhouse/expeditions'));
    if (expedition) found.value = expedition.data;
    else releaseAchievements();
}

watch(found, (expedition) => {
    if (expedition === null) releaseAchievements();
});
onUnmounted(releaseAchievements);
</script>

<template>
    <div class="page page--wide greenhouse">
        <PageHeader :title="t('greenhouse.title')" :subtitle="subtitle" />
        <template v-if="greenhouse">
            <GreenhouseResources :greenhouse="greenhouse" :tank="tank" :ratio="ratio" :seconds-until-full="untilFull" :collecting="collecting" @collect="collect" />

            <PageSection :title="t('greenhouse.pots.title')">
                <p v-if="!greenhouse.plantable.length" class="greenhouse__hint">{{ t('greenhouse.pots.noMoss') }}</p>
                <PotTable ref="potTable" :pots="greenhouse.pots" :max-pots="greenhouse.maxPots" :busy="busy" @plant="choosePot" @unplant="unplant" />
            </PageSection>

            <PageSection :title="t('greenhouse.facilities.title')">
                <FacilityTable :facilities="greenhouse.facilities" :dew="greenhouse.dew" :busy="busy" @upgrade="upgrade" />
            </PageSection>

            <div class="greenhouse__columns">
                <PageSection :title="t('greenhouse.expedition.section')">
                    <ExpeditionPanel v-model:found="found" :expedition="greenhouse.expedition" :dew="greenhouse.dew" :busy="busy" @launch="launch" />
                </PageSection>
                <PageSection :title="t('greenhouse.sources.title')">
                    <DewSources :sources="greenhouse.taskDew" :watering-hours="greenhouse.wateringHours" />
                </PageSection>
            </div>

            <PlantDialog v-model:open="plantOpen" :pot="plantingPot" :plantable="greenhouse.plantable" :busy="busy" @choose="plant" @close-auto-focus="returnToPot" />
        </template>
    </div>
</template>

<style scoped>
.greenhouse__hint { max-width: 40rem; color: var(--color-muted); font-size: var(--font-size-md); }
.greenhouse__columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; gap: var(--space-5); }

@media (max-width: 64rem) {
    .greenhouse__columns { grid-template-columns: minmax(0, 1fr); }
}
</style>
