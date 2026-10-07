<script setup>
import { computed, ref } from 'vue';
import { AlignLeft, CornerDownRight, ListChecks, Repeat, Sun } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import IconButton from '../ui/IconButton.vue';
import DropCheck from './DropCheck.vue';
import PlanMenu from './PlanMenu.vue';
import TaskMenu from './TaskMenu.vue';
import { useDates } from '../../composables/useDates.js';
import { useProjects } from '../../composables/useProjects.js';
import { useTaskActions } from '../../composables/useTaskActions.js';
import { useTaskEditor } from '../../composables/useTaskEditor.js';
import { isOverdue, isPlannedFor } from '../../tasks/days.js';
import { recurrenceLabel } from '../../tasks/recurrence.js';
import { hasOpenSubtasks } from '../../tasks/subtasks.js';

const props = defineProps({
    task: { type: Object, required: true },
    showProject: { type: Boolean, default: true },
    showPlanned: { type: Boolean, default: true },
    compact: { type: Boolean, default: false },
    nested: { type: Boolean, default: false },
});

const { t } = useI18n();
const dates = useDates();
const { byId } = useProjects();
const actions = useTaskActions();
const editor = useTaskEditor();
const planOpen = ref(false);

const project = computed(() => (props.task.projectId ? byId.value.get(props.task.projectId) : null));
const inToday = computed(() => isPlannedFor(props.task, dates.today.value));
const overdue = computed(() => isOverdue(props.task, dates.today.value));

function dayLabel(day) {
    const relative = dates.relative(day);
    if (relative === 'weekday') return dates.weekday(day);
    if (relative === 'past' || relative === 'later') return dates.short(day);
    return t(`tasks.days.${relative}`);
}

const plannedLabel = computed(() => {
    if (!props.showPlanned || !props.task.plannedOn || props.task.done) return null;
    return dayLabel(props.task.plannedOn);
});

const locked = computed(() => hasOpenSubtasks(props.task));
const parentLabel = computed(() => (props.nested ? null : props.task.parentTitle));
const checkLabel = computed(() => {
    if (locked.value) return t('tasks.subtasks.locked', { title: props.task.title });
    return t(props.task.done ? 'tasks.reopen' : 'tasks.complete', { title: props.task.title });
});

const repeatLabel = computed(() => recurrenceLabel(props.task.recurrence, t));

const dueLabel = computed(() => (props.task.dueOn ? t('tasks.due', { day: dayLabel(props.task.dueOn) }) : null));

function toggle() {
    if (locked.value) return null;
    return props.task.done ? actions.reopen(props.task) : actions.complete(props.task);
}

function plan(when, date = null) {
    actions.plan(props.task, when, date);
}

function onKeydown(event) {
    if (event.target !== event.currentTarget || event.metaKey || event.ctrlKey || event.altKey) return;
    const shortcuts = {
        t: () => plan('today'),
        m: () => plan('tomorrow'),
        n: () => plan('next_week'),
        d: () => { planOpen.value = true; },
        e: () => editor.edit(props.task),
        Enter: () => editor.edit(props.task),
        ' ': () => toggle(),
    };
    if (shortcuts[event.key]) {
        event.preventDefault();
        shortcuts[event.key]();
    }
}
</script>

<template>
    <li
        :class="['task-row', `task-row--${task.quadrant ?? 'unsorted'}`, { 'task-row--done': task.done, 'task-row--compact': compact, 'task-row--nested': nested }]"
        tabindex="0"
        :data-task="task.id"
        @keydown="onKeydown"
    >
        <DropCheck :done="task.done" :disabled="locked" :label="checkLabel" @toggle="toggle" />
        <div class="task-row__main">
            <button type="button" class="task-row__title" @click="editor.edit(task)">{{ task.title }}</button>
            <p v-if="parentLabel || (showProject && project) || task.subtaskCount || plannedLabel || dueLabel || repeatLabel || task.notes" class="task-row__meta">
                <span v-if="parentLabel" class="task-row__parent"><CornerDownRight size="0.875rem" aria-hidden="true" />{{ t('tasks.subtasks.of', { title: parentLabel }) }}</span>
                <span v-if="showProject && project" class="task-row__project" :style="{ '--project': `var(--project-${project.color})` }">{{ project.name }}</span>
                <span v-if="task.subtaskCount" class="task-row__subtasks" :aria-label="t('tasks.subtasks.progress', { done: task.subtasksDone, total: task.subtaskCount })">
                    <ListChecks size="0.875rem" aria-hidden="true" /><span aria-hidden="true">{{ task.subtasksDone }}/{{ task.subtaskCount }}</span>
                </span>
                <span v-if="plannedLabel" class="task-row__planned">{{ plannedLabel }}</span>
                <span v-if="dueLabel" :class="['task-row__due', { 'task-row__due--overdue': overdue }]">{{ dueLabel }}</span>
                <span v-if="repeatLabel" class="task-row__repeat"><Repeat size="0.875rem" aria-hidden="true" />{{ repeatLabel }}</span>
                <AlignLeft v-if="task.notes" size="0.875rem" class="task-row__notes" :aria-label="t('tasks.hasNotes')" role="img" />
            </p>
        </div>
        <div v-if="!task.done" class="task-row__actions">
            <IconButton
                v-if="!compact"
                :icon="Sun"
                :label="inToday ? t('tasks.removeFromToday') : t('tasks.addToToday')"
                :pressed="inToday"
                class="task-row__sun"
                @click="plan(inToday ? 'none' : 'today')"
            />
            <PlanMenu v-if="!compact" v-model:open="planOpen" :task="task" @plan="plan" />
            <TaskMenu :task="task" @edit="editor.edit(task)" @classify="(quadrant) => actions.classify(task, quadrant)" />
        </div>
    </li>
</template>

<style scoped>
.task-row {
    --notch: var(--quadrant-unsorted);
    position: relative;
    display: flex;
    align-items: center;
    gap: var(--space-3);
    min-height: 3.25rem;
    padding: var(--space-2) var(--space-2) var(--space-2) var(--space-4);
    border: 0.0625rem solid var(--color-border);
    border-radius: var(--radius);
    background: var(--color-surface);
    outline: none;
    transition: background var(--transition), opacity var(--transition);
}

.task-row::before { content: ""; position: absolute; top: 0.625rem; bottom: 0.625rem; left: -0.0625rem; width: 0.1875rem; border-radius: 0 0.125rem 0.125rem 0; background: var(--notch); }
.task-row:hover, .task-row:focus-within { border-color: var(--color-border-strong); }
.task-row:focus-visible { box-shadow: var(--focus-ring); }

.task-row--do_first { --notch: var(--quadrant-do-first); }
.task-row--schedule { --notch: var(--quadrant-schedule); }
.task-row--delegate { --notch: var(--quadrant-delegate); }
.task-row--eliminate { --notch: var(--quadrant-eliminate); }

.task-row__main { display: flex; flex: 1; flex-direction: column; min-width: 0; }
.task-row__title { padding: 0; border: none; background: none; color: var(--color-ink); font-size: var(--font-size); text-align: left; overflow-wrap: anywhere; cursor: pointer; }
.task-row__title:hover { color: var(--color-accent-strong); }
.task-row__meta { display: flex; flex-wrap: wrap; align-items: center; gap: var(--space-1) var(--space-3); color: var(--color-muted); font-size: var(--font-size-sm); }
.task-row__project { display: inline-flex; align-items: center; gap: var(--space-1); }
.task-row__project::before { content: ""; width: 0.5rem; height: 0.5rem; border-radius: 50%; background: var(--project); }
.task-row__due--overdue { color: var(--color-danger); font-weight: 600; }
.task-row__repeat, .task-row__parent, .task-row__subtasks { display: inline-flex; align-items: center; gap: var(--space-1); }
.task-row__parent { min-width: 0; overflow-wrap: anywhere; }
.task-row__subtasks { font-variant-numeric: tabular-nums; }
.task-row__notes { color: var(--color-subtle); }
.task-row__actions { display: flex; align-items: center; flex-shrink: 0; }
.task-row__sun[aria-pressed="true"] { color: var(--color-warning); }

.task-row--done { border-color: transparent; background: transparent; }
.task-row--done::before { opacity: 0.3; }
.task-row--done .task-row__title { color: var(--color-subtle); text-decoration: line-through; text-decoration-color: var(--color-border-strong); }

.task-row--nested { min-height: 2.75rem; }

.task-row--compact { min-height: 3rem; gap: var(--space-2); padding-left: var(--space-3); cursor: grab; }
.task-row--compact .task-row__title { font-size: var(--font-size-md); }

@media (max-width: 30rem) {
    .task-row { gap: var(--space-2); padding-left: var(--space-3); }
    .task-row__sun { display: none; }
}
</style>
