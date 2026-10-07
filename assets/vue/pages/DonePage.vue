<script setup>
import { computed, effectScope, onScopeDispose, ref, shallowRef } from 'vue';
import { useI18n } from 'vue-i18n';
import BaseButton from '../components/ui/BaseButton.vue';
import EmptyState from '../components/ui/EmptyState.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import PageSection from '../components/ui/PageSection.vue';
import TaskList from '../components/tasks/TaskList.vue';
import { useApi } from '../composables/useApi.js';
import { useDates } from '../composables/useDates.js';
import { useToast } from '../composables/useToast.js';

const { t } = useI18n();
const api = useApi();
const dates = useDates();
const toast = useToast();

const scope = effectScope();
onScopeDispose(() => scope.stop());

const windows = shallowRef([]);
const loadingOlder = ref(false);

function loadWindow(before) {
    const view = ref(null);
    windows.value = [...windows.value, view];
    return scope.run(() => api.load(before ? `/api/tasks/done?before=${before}` : '/api/tasks/done', view));
}

loadWindow(null);

const first = computed(() => windows.value[0]?.value ?? null);
const days = computed(() => windows.value.flatMap((view) => view.value?.days ?? []));
const older = computed(() => windows.value.at(-1)?.value?.older ?? null);
const subtitle = computed(() => (first.value ? t('done.subtitle', first.value.days.reduce((sum, day) => sum + day.tasks.length, 0)) : null));

function heading(day) {
    const relative = dates.relative(day);
    return relative === 'today' || relative === 'yesterday' ? t(`tasks.days.${relative}`) : dates.long(day);
}

async function showOlder() {
    loadingOlder.value = true;
    try {
        await loadWindow(older.value);
    } catch (error) {
        toast.error(error.message);
    } finally {
        loadingOlder.value = false;
    }
}
</script>

<template>
    <div class="page">
        <PageHeader :title="t('done.title')" :subtitle="subtitle" />
        <template v-if="first">
            <EmptyState v-if="!days.length" :title="t(older ? 'done.quiet.title' : 'done.empty.title')" :hint="t(older ? 'done.quiet.hint' : 'done.empty.hint')" />
            <PageSection v-for="day in days" :key="day.date" :title="heading(day.date)" :count="day.tasks.length">
                <TaskList :tasks="day.tasks" :sorted="false" :show-planned="false" />
            </PageSection>
            <BaseButton v-if="older" class="done__older" variant="secondary" :loading="loadingOlder" @click="showOlder">{{ t('done.older') }}</BaseButton>
        </template>
    </div>
</template>

<style scoped>
.done__older { align-self: center; }
</style>
