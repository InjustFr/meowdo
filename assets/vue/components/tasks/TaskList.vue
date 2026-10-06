<script setup>
import { computed } from 'vue';
import TaskRow from './TaskRow.vue';
import { sortTasks } from '../../tasks/compareTasks.js';

const props = defineProps({
    tasks: { type: Array, required: true },
    sorted: { type: Boolean, default: true },
    showProject: { type: Boolean, default: true },
    showPlanned: { type: Boolean, default: true },
});

const ordered = computed(() => (props.sorted ? sortTasks(props.tasks) : props.tasks));
</script>

<template>
    <TransitionGroup tag="ul" name="task-list" class="task-list">
        <TaskRow v-for="task in ordered" :key="task.id" :task="task" :show-project="showProject" :show-planned="showPlanned" />
    </TransitionGroup>
</template>

<style scoped>
.task-list { display: flex; flex-direction: column; gap: var(--space-2); margin: 0; padding: 0; list-style: none; }
.task-list-move { transition: transform 260ms ease; }
.task-list-enter-from { opacity: 0; transform: translateY(-0.25rem); }
.task-list-enter-active { transition: opacity 200ms ease, transform 200ms ease; }
.task-list-leave-active { display: none; }
</style>
