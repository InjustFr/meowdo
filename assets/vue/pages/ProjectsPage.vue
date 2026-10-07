<script setup>
import { ref } from 'vue';
import { RouterLink } from 'vue-router';
import { ChartColumn, CircleCheckBig, Plus, Settings, Trophy } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import BaseButton from '../components/ui/BaseButton.vue';
import EmptyState from '../components/ui/EmptyState.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import PageSection from '../components/ui/PageSection.vue';
import ProjectEditor from '../components/projects/ProjectEditor.vue';
import { useProjects } from '../composables/useProjects.js';

const { t } = useI18n();
const { projects } = useProjects();
const creating = ref(false);

const MORE = [
    { to: '/done', icon: CircleCheckBig, label: 'nav.done' },
    { to: '/stats', icon: ChartColumn, label: 'nav.stats' },
    { to: '/achievements', icon: Trophy, label: 'nav.achievements' },
    { to: '/settings', icon: Settings, label: 'nav.settings' },
];
</script>

<template>
    <div class="page">
        <PageHeader :title="t('projects.title')">
            <BaseButton @click="creating = true"><Plus size="1rem" aria-hidden="true" />{{ t('projects.new') }}</BaseButton>
        </PageHeader>
        <EmptyState v-if="!projects.length" :title="t('projects.none_yet.title')" :hint="t('projects.none_yet.hint')" />
        <ul v-else class="projects">
            <li v-for="project in projects" :key="project.id">
                <RouterLink :to="`/projects/${project.id}`" class="projects__link" :style="{ '--project': `var(--project-${project.color})` }">
                    <span class="projects__name">{{ project.name }}</span>
                    <span class="projects__count tabular">{{ project.openTasks }}</span>
                </RouterLink>
            </li>
            <li>
                <RouterLink to="/inbox" class="projects__link projects__link--inbox">
                    <span class="projects__name">{{ t('nav.inbox') }}</span>
                </RouterLink>
            </li>
        </ul>
        <PageSection :title="t('nav.more')">
            <ul class="projects">
                <li v-for="link in MORE" :key="link.to">
                    <RouterLink :to="link.to" class="projects__link projects__link--more">
                        <component :is="link.icon" class="projects__icon" size="1.125rem" :stroke-width="1.75" aria-hidden="true" />
                        <span class="projects__name">{{ t(link.label) }}</span>
                    </RouterLink>
                </li>
            </ul>
        </PageSection>
        <ProjectEditor v-model:open="creating" @saved="(project) => project && $router.push(`/projects/${project.id}`)" />
    </div>
</template>

<style scoped>
.projects { display: flex; flex-direction: column; gap: var(--space-2); margin: 0; padding: 0; list-style: none; }
.projects__link { --project: var(--color-border-strong); display: flex; align-items: center; gap: var(--space-3); min-height: 3.5rem; padding: var(--space-2) var(--space-4); border-radius: var(--radius); background: var(--color-surface); color: var(--color-text); text-decoration: none; }
.projects__link::before { content: ""; width: 0.75rem; height: 0.75rem; border-radius: 50%; background: var(--project); }
.projects__link:hover { background: var(--color-surface); }
.projects__name { flex: 1; font-weight: 700; }
.projects__count { color: var(--color-muted); }
.projects__link--more::before { display: none; }
.projects__icon { flex-shrink: 0; color: var(--color-subtle); }
</style>
