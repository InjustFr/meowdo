<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { percent } from '../../stats/scale.js';

const props = defineProps({
    projects: { type: Array, required: true },
});

const { t } = useI18n();

const highest = computed(() => Math.max(1, ...props.projects.map((project) => project.count)));
</script>

<template>
    <ul class="project-counts">
        <li v-for="project in projects" :key="project.id ?? 'inbox'" class="project-counts__item" :style="{ '--project': project.color ? `var(--project-${project.color})` : 'var(--color-border-strong)' }">
            <RouterLink :to="project.id ? `/projects/${project.id}` : '/inbox'" class="project-counts__name">{{ project.name ?? t('nav.inbox') }}</RouterLink>
            <span class="project-counts__count tabular">{{ project.count }}</span>
            <span class="project-counts__track" aria-hidden="true"><span class="project-counts__bar" :style="{ width: `${percent(project.count, highest)}%` }" /></span>
        </li>
    </ul>
</template>

<style scoped>
.project-counts { display: flex; flex-direction: column; gap: var(--space-3); margin: 0; padding: 0; list-style: none; }
.project-counts__item { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: var(--space-1) var(--space-3); font-size: var(--font-size-md); }
.project-counts__name { display: flex; align-items: center; gap: var(--space-2); min-width: 0; overflow: hidden; color: var(--color-text); text-decoration: none; text-overflow: ellipsis; white-space: nowrap; }
.project-counts__name::before { content: ""; flex-shrink: 0; width: 0.625rem; height: 0.625rem; border-radius: 50%; background: var(--project); }
.project-counts__name:hover { color: var(--color-ink); text-decoration: underline; text-decoration-color: var(--color-border-strong); }
.project-counts__count { color: var(--color-ink); font-weight: 600; }
.project-counts__track { grid-column: 1 / -1; height: 0.375rem; }
.project-counts__bar { display: block; height: 100%; min-width: 0.25rem; border-radius: var(--radius-sm); background: var(--project); }
</style>
