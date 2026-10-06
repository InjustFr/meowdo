<script setup>
import { ref, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import { useI18n } from 'vue-i18n';
import PageHeader from '../components/ui/PageHeader.vue';
import TaskRow from '../components/tasks/TaskRow.vue';
import { useApi } from '../composables/useApi.js';
import { useTaskActions } from '../composables/useTaskActions.js';
import { sortTasks } from '../tasks/compareTasks.js';
import { QUADRANTS } from '../tasks/quadrants.js';

const { t } = useI18n();
const actions = useTaskActions();
const tasks = ref(null);
useApi().load('/api/matrix', tasks);

const zones = ref({});
const unsorted = ref([]);

watch(tasks, (value) => {
    if (!value) return;
    zones.value = Object.fromEntries(QUADRANTS.map((quadrant) => [quadrant.value, sortTasks(value.filter((task) => task.quadrant === quadrant.value))]));
    unsorted.value = sortTasks(value.filter((task) => task.quadrant === null));
}, { immediate: true });

function arranged(quadrant) {
    actions.reorder(quadrant, zones.value[quadrant].map((task) => task.id));
}

function unclassified(event) {
    const task = tasks.value.find((candidate) => candidate.id === event.item.dataset.task);
    if (task) actions.classify(task, null);
}

const draggable = { group: 'matrix', animation: 160, delay: 180, delayOnTouchOnly: true, ghostClass: 'matrix__ghost' };
</script>

<template>
    <div class="page page--wide">
        <PageHeader :title="t('matrix.title')" :subtitle="t('matrix.subtitle')" />
        <div v-if="tasks" class="matrix">
            <p class="matrix__axis matrix__axis--top" aria-hidden="true"><span>{{ t('matrix.urgent') }}</span><span>{{ t('matrix.notUrgent') }}</span></p>
            <p class="matrix__axis matrix__axis--side" aria-hidden="true"><span>{{ t('matrix.important') }}</span><span>{{ t('matrix.notImportant') }}</span></p>
            <div class="matrix__board">
                <section v-for="quadrant in QUADRANTS" :key="quadrant.value" :class="['matrix__zone', `matrix__zone--${quadrant.value}`]">
                    <header class="matrix__header">
                        <h2 class="matrix__name">{{ t(`matrix.quadrants.${quadrant.value}.name`) }}</h2>
                        <p class="matrix__plain">{{ t(`matrix.quadrants.${quadrant.value}.plain`) }} · {{ t(`matrix.quadrants.${quadrant.value}.advice`) }}</p>
                    </header>
                    <VueDraggable v-model="zones[quadrant.value]" v-bind="draggable" tag="ul" class="matrix__tasks" @update="arranged(quadrant.value)" @add="arranged(quadrant.value)">
                        <TaskRow v-for="task in zones[quadrant.value]" :key="task.id" :task="task" compact />
                    </VueDraggable>
                </section>
            </div>
            <section class="matrix__tray">
                <header class="matrix__header">
                    <h2>{{ t('matrix.unsorted') }}</h2>
                    <p class="matrix__plain">{{ t('matrix.unsortedHint') }}</p>
                </header>
                <VueDraggable v-model="unsorted" v-bind="draggable" tag="ul" class="matrix__tasks matrix__tasks--tray" @add="unclassified">
                    <TaskRow v-for="task in unsorted" :key="task.id" :task="task" compact />
                </VueDraggable>
            </section>
        </div>
    </div>
</template>

<style scoped>
.matrix { display: grid; grid-template-columns: auto minmax(0, 1fr); grid-template-areas: ". top" "side board" ". tray"; gap: var(--space-2) var(--space-3); }
.matrix__axis { display: flex; color: var(--color-subtle); font-size: var(--font-size-xs); font-weight: 700; }
.matrix__axis span { flex: 1; text-align: center; }
.matrix__axis--top { grid-area: top; }
.matrix__axis--side { grid-area: side; flex-direction: row-reverse; writing-mode: vertical-rl; transform: rotate(180deg); }
.matrix__axis--side span { display: flex; align-items: center; justify-content: center; }
.matrix__board { grid-area: board; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--space-3); }
.matrix__zone { --tone: var(--quadrant-unsorted); display: flex; flex-direction: column; gap: var(--space-3); min-height: 14rem; padding: var(--space-4); border-radius: var(--radius-panel); background: color-mix(in oklch, var(--tone) 9%, var(--night)); box-shadow: inset 0 0 0 0.0625rem color-mix(in oklch, var(--tone) 30%, transparent); }
.matrix__zone--do_first { --tone: var(--quadrant-do-first); }
.matrix__zone--schedule { --tone: var(--quadrant-schedule); }
.matrix__zone--delegate { --tone: var(--quadrant-delegate); }
.matrix__zone--eliminate { --tone: var(--quadrant-eliminate); }
.matrix__name { color: var(--tone); font-family: var(--font-display); font-size: 1.5rem; font-weight: 400; }
.matrix__plain { color: var(--color-muted); font-size: var(--font-size-sm); }
.matrix__tasks { display: flex; flex: 1; flex-direction: column; gap: var(--space-2); min-height: 4rem; margin: 0; padding: 0; list-style: none; }
.matrix__tray { grid-area: tray; display: flex; flex-direction: column; gap: var(--space-3); margin-top: var(--space-3); padding: var(--space-4); border: 0.125rem dashed var(--color-line-strong); border-radius: var(--radius-panel); }
.matrix__tasks--tray { display: grid; grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr)); }
:deep(.matrix__ghost) { opacity: 0.4; }

@media (max-width: 48rem) {
    .matrix { grid-template-columns: 1fr; grid-template-areas: "board" "tray"; }
    .matrix__axis { display: none; }
    .matrix__board { grid-template-columns: 1fr; }
    .matrix__zone { min-height: 0; }
}
</style>
