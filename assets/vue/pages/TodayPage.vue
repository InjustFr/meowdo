<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import BaseButton from '../components/ui/BaseButton.vue';
import EmptyState from '../components/ui/EmptyState.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import PageSection from '../components/ui/PageSection.vue';
import TaskComposer from '../components/tasks/TaskComposer.vue';
import TaskList from '../components/tasks/TaskList.vue';
import { useApi } from '../composables/useApi.js';
import { useDates } from '../composables/useDates.js';
import { useTaskActions } from '../composables/useTaskActions.js';

const { t } = useI18n();
const api = useApi();
const dates = useDates();
const actions = useTaskActions();

const view = ref(null);
api.load('/api/tasks/today', view);

const open = computed(() => (view.value?.today ?? []).filter((task) => !task.done));
const subtitle = computed(() => {
    if (!view.value) return null;
    return `${dates.long(view.value.date)} · ${t('today.left', open.value.length)}`;
});
const empty = computed(() => view.value && !view.value.today.length && !view.value.earlier.length && !view.value.done.length);
</script>

<template>
    <div class="page">
        <PageHeader :title="t('today.title')" :subtitle="subtitle" />
        <TaskComposer :defaults="{ plan: 'today' }" :placeholder="t('today.composer')" />
        <template v-if="view">
            <EmptyState v-if="empty" :title="t('today.empty.title')" :hint="t('today.empty.hint')" />
            <TaskList v-else-if="view.today.length" :tasks="view.today" :show-planned="false" />
            <PageSection v-if="view.earlier.length" :title="t('today.earlier')" :count="view.earlier.length">
                <template #actions>
                    <BaseButton variant="secondary" @click="actions.moveOverdueToToday()">{{ t('today.moveAll') }}</BaseButton>
                </template>
                <TaskList :tasks="view.earlier" />
            </PageSection>
            <PageSection v-if="view.done.length" :title="t('today.done')" :count="view.done.length">
                <TaskList :tasks="view.done" :sorted="false" :show-planned="false" />
            </PageSection>
        </template>
    </div>
</template>
