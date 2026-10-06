<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import EmptyState from '../components/ui/EmptyState.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import TaskComposer from '../components/tasks/TaskComposer.vue';
import TaskList from '../components/tasks/TaskList.vue';
import { useApi } from '../composables/useApi.js';

const { t } = useI18n();
const tasks = ref(null);
useApi().load('/api/tasks/inbox', tasks);
</script>

<template>
    <div class="page">
        <PageHeader :title="t('inbox.title')" :subtitle="t('inbox.subtitle')" />
        <TaskComposer />
        <template v-if="tasks">
            <EmptyState v-if="!tasks.length" :title="t('inbox.empty.title')" :hint="t('inbox.empty.hint')" />
            <TaskList v-else :tasks="tasks" :show-project="false" />
        </template>
    </div>
</template>
