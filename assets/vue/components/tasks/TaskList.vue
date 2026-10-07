<script setup>
import { computed } from 'vue';
import TaskRow from './TaskRow.vue';
import { sortTasks } from '../../tasks/compareTasks.js';
import { nestSubtasks } from '../../tasks/subtasks.js';

const props = defineProps({
    tasks: { type: Array, required: true },
    sorted: { type: Boolean, default: true },
    showProject: { type: Boolean, default: true },
    showPlanned: { type: Boolean, default: true },
});

const nested = computed(() => nestSubtasks(props.sorted ? sortTasks(props.tasks) : props.tasks));
</script>

<template>
    <TransitionGroup tag="ul" name="task-list" class="task-list">
        <template v-for="{ task, subtasks } in nested" :key="task.id">
            <TaskRow :task="task" :show-project="showProject" :show-planned="showPlanned" />
            <li v-if="subtasks.length" class="task-list__subtasks">
                <ul class="task-list">
                    <TaskRow v-for="subtask in subtasks" :key="subtask.id" :task="subtask" :show-project="false" :show-planned="showPlanned" nested />
                </ul>
            </li>
        </template>
    </TransitionGroup>
</template>

<style scoped>
.task-list { display: flex; flex-direction: column; gap: var(--space-2); margin: 0; padding: 0; list-style: none; }
.task-list__subtasks { margin-left: var(--space-6); padding-left: var(--space-3); border-left: 0.125rem solid var(--color-border); }
.task-list-move { transition: transform 260ms ease; }
.task-list-enter-from { opacity: 0; transform: translateY(-0.25rem); }
.task-list-enter-active { transition: opacity 200ms ease, transform 200ms ease; }
.task-list-leave-active { display: none; }

@media (max-width: 30rem) {
    .task-list__subtasks { margin-left: var(--space-3); padding-left: var(--space-2); }
}
</style>
