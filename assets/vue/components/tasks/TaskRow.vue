<script setup>
import { computed, ref } from 'vue';
import { AlignLeft, Sun } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import IconButton from '../ui/IconButton.vue';
import PawCheck from './PawCheck.vue';
import PlanMenu from './PlanMenu.vue';
import TaskMenu from './TaskMenu.vue';
import { useDates } from '../../composables/useDates.js';
import { useProjects } from '../../composables/useProjects.js';
import { useTaskActions } from '../../composables/useTaskActions.js';
import { useTaskEditor } from '../../composables/useTaskEditor.js';
import { isOverdue, isPlannedFor } from '../../tasks/days.js';

const props = defineProps({
    task: { type: Object, required: true },
    showProject: { type: Boolean, default: true },
    showPlanned: { type: Boolean, default: true },
    compact: { type: Boolean, default: false },
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

const dueLabel = computed(() => (props.task.dueOn ? t('tasks.due', { day: dayLabel(props.task.dueOn) }) : null));

function toggle() {
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
        :class="['task-row', `task-row--${task.quadrant ?? 'unsorted'}`, { 'task-row--done': task.done, 'task-row--compact': compact }]"
        tabindex="0"
        :data-task="task.id"
        @keydown="onKeydown"
    >
        <PawCheck :done="task.done" :label="t(task.done ? 'tasks.reopen' : 'tasks.complete', { title: task.title })" @toggle="toggle" />
        <div class="task-row__main">
            <button type="button" class="task-row__title" @click="editor.edit(task)">{{ task.title }}</button>
            <p v-if="(showProject && project) || plannedLabel || dueLabel || task.notes" class="task-row__meta">
                <span v-if="showProject && project" class="task-row__project" :style="{ '--project': `var(--project-${project.color})` }">{{ project.name }}</span>
                <span v-if="plannedLabel" class="task-row__planned">{{ plannedLabel }}</span>
                <span v-if="dueLabel" :class="['task-row__due', { 'task-row__due--overdue': overdue }]">{{ dueLabel }}</span>
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
    min-height: 3.5rem;
    padding: var(--space-2) var(--space-2) var(--space-2) var(--space-4);
    border-radius: var(--radius-row);
    background: var(--color-surface);
    outline: none;
    transition: background var(--transition), opacity var(--transition);
}

.task-row::before { content: ""; position: absolute; top: 0.625rem; bottom: 0.625rem; left: 0; width: 0.25rem; border-radius: 0 0.25rem 0.25rem 0; background: var(--notch); }
.task-row:hover, .task-row:focus-within { background: var(--color-raised); }
.task-row:focus-visible { box-shadow: var(--focus-ring); }

.task-row--do_first { --notch: var(--quadrant-do-first); }
.task-row--schedule { --notch: var(--quadrant-schedule); }
.task-row--delegate { --notch: var(--quadrant-delegate); }
.task-row--eliminate { --notch: var(--quadrant-eliminate); }

.task-row__main { display: flex; flex: 1; flex-direction: column; min-width: 0; }
.task-row__title { padding: 0; border: none; background: none; color: var(--color-text); font-size: var(--font-size); text-align: left; overflow-wrap: anywhere; cursor: pointer; }
.task-row__title:hover { color: var(--color-accent); }
.task-row__meta { display: flex; flex-wrap: wrap; align-items: center; gap: var(--space-1) var(--space-3); color: var(--color-muted); font-size: var(--font-size-sm); }
.task-row__project { display: inline-flex; align-items: center; gap: var(--space-1); }
.task-row__project::before { content: ""; width: 0.5rem; height: 0.5rem; border-radius: 50%; background: var(--project); }
.task-row__due--overdue { color: var(--coral); font-weight: 700; }
.task-row__notes { color: var(--color-subtle); }
.task-row__actions { display: flex; align-items: center; flex-shrink: 0; }
.task-row__sun[aria-pressed="true"] { color: var(--lamp); }

.task-row--done { background: transparent; }
.task-row--done::before { opacity: 0.3; }
.task-row--done .task-row__title { color: var(--color-subtle); text-decoration: line-through; text-decoration-color: var(--color-line-strong); }

.task-row--compact { min-height: 3rem; gap: var(--space-2); padding-left: var(--space-3); cursor: grab; }
.task-row--compact .task-row__title { font-size: var(--font-size-md); }

@media (max-width: 30rem) {
    .task-row { gap: var(--space-2); padding-left: var(--space-3); }
    .task-row__sun { display: none; }
}
</style>
