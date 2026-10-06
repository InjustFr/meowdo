<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink, RouterView } from 'vue-router';
import { CalendarDays, FolderOpen, Grid2x2, Inbox, Plus, Settings, Shirt, Sun, Trophy } from '@lucide/vue';
import { ConfigProvider, TooltipProvider } from 'reka-ui';
import { useI18n } from 'vue-i18n';
import CatDesk from '../components/cat/CatDesk.vue';
import CelebrationLayer from '../components/cat/CelebrationLayer.vue';
import IconButton from '../components/ui/IconButton.vue';
import ProjectEditor from '../components/projects/ProjectEditor.vue';
import TaskEditor from '../components/tasks/TaskEditor.vue';
import ToastHost from '../components/ui/ToastHost.vue';
import { usePlayer } from '../composables/usePlayer.js';
import { useProjects } from '../composables/useProjects.js';
import { useSync } from '../composables/useSync.js';
import { useTaskEditor } from '../composables/useTaskEditor.js';
import { intlLocale } from '../i18n/locale.js';

const { t } = useI18n();
const { projects, load: loadProjects } = useProjects();
const { load: loadPlayer } = usePlayer();
const editor = useTaskEditor();
const creatingProject = ref(false);

loadProjects();
loadPlayer();
useSync();

const MAIN = [
    { to: '/', icon: Sun, label: 'nav.today' },
    { to: '/upcoming', icon: CalendarDays, label: 'nav.upcoming' },
    { to: '/inbox', icon: Inbox, label: 'nav.inbox' },
    { to: '/matrix', icon: Grid2x2, label: 'nav.matrix' },
];

const SECONDARY = [
    { to: '/shop', icon: Shirt, label: 'nav.shop' },
    { to: '/achievements', icon: Trophy, label: 'nav.achievements' },
    { to: '/settings', icon: Settings, label: 'nav.settings' },
];

const TABS = [
    { to: '/', icon: Sun, label: 'nav.today' },
    { to: '/upcoming', icon: CalendarDays, label: 'nav.upcoming' },
    { to: '/matrix', icon: Grid2x2, label: 'nav.matrix' },
    { to: '/projects', icon: FolderOpen, label: 'nav.projects' },
    { to: '/shop', icon: Shirt, label: 'nav.cat' },
];

function focusComposer(event) {
    const typing = event.target.closest?.('input, textarea, [contenteditable], [role="dialog"], [role="menu"]');
    if (typing || event.metaKey || event.ctrlKey || event.altKey || event.key !== 'n') return;
    const composer = document.getElementById('task-composer-input');
    if (composer) {
        event.preventDefault();
        composer.focus();
    }
}

onMounted(() => document.addEventListener('keydown', focusComposer));
onBeforeUnmount(() => document.removeEventListener('keydown', focusComposer));
</script>

<template>
    <ConfigProvider :locale="intlLocale()">
        <TooltipProvider :delay-duration="400">
            <div class="shell">
                <a class="shell__skip" href="#main">{{ t('nav.skip') }}</a>
                <nav class="shell__rail" :aria-label="t('nav.label')">
                    <RouterLink to="/" class="shell__brand">Meowdo</RouterLink>
                    <ul class="shell__links">
                        <li v-for="link in MAIN" :key="link.to">
                            <RouterLink :to="link.to" class="shell__link" exact-active-class="shell__link--active">
                                <component :is="link.icon" size="1.125rem" aria-hidden="true" />{{ t(link.label) }}
                            </RouterLink>
                        </li>
                    </ul>
                    <div class="shell__projects">
                        <div class="shell__projects-header">
                            <h2 class="shell__projects-title">{{ t('nav.projects') }}</h2>
                            <IconButton :icon="Plus" :label="t('projects.new')" @click="creatingProject = true" />
                        </div>
                        <ul class="shell__links">
                            <li v-for="project in projects" :key="project.id">
                                <RouterLink :to="`/projects/${project.id}`" class="shell__link shell__link--project" active-class="shell__link--active" :style="{ '--project': `var(--project-${project.color})` }">
                                    <span class="shell__project-name">{{ project.name }}</span>
                                    <span v-if="project.openTasks" class="shell__count tabular">{{ project.openTasks }}</span>
                                </RouterLink>
                            </li>
                        </ul>
                        <p v-if="!projects.length" class="shell__hint">{{ t('nav.noProjects') }}</p>
                    </div>
                    <ul class="shell__links shell__links--secondary">
                        <li v-for="link in SECONDARY" :key="link.to">
                            <RouterLink :to="link.to" class="shell__link" active-class="shell__link--active">
                                <component :is="link.icon" size="1.125rem" aria-hidden="true" />{{ t(link.label) }}
                            </RouterLink>
                        </li>
                    </ul>
                </nav>

                <main id="main" class="shell__main">
                    <div class="shell__strip"><CatDesk compact /></div>
                    <RouterView />
                </main>

                <div class="shell__desk"><CatDesk /></div>

                <nav class="shell__tabs" :aria-label="t('nav.label')">
                    <RouterLink v-for="tab in TABS" :key="tab.to" :to="tab.to" class="shell__tab" :exact-active-class="tab.to === '/' ? 'shell__tab--active' : undefined" :active-class="tab.to === '/' ? undefined : 'shell__tab--active'">
                        <component :is="tab.icon" size="1.25rem" aria-hidden="true" />
                        <span>{{ t(tab.label) }}</span>
                    </RouterLink>
                </nav>
            </div>

            <TaskEditor v-model:open="editor.state.open" :task="editor.state.task" />
            <ProjectEditor v-model:open="creatingProject" @saved="(project) => project && $router.push(`/projects/${project.id}`)" />
            <CelebrationLayer />
            <ToastHost />
        </TooltipProvider>
    </ConfigProvider>
</template>

<style scoped>
.shell {
    display: grid;
    grid-template-columns: var(--rail-width) minmax(0, 1fr) var(--desk-width);
    min-height: 100vh;
}

.shell__skip { position: absolute; top: -10rem; left: var(--space-4); z-index: 100; padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill); background: var(--lamp); color: var(--night); font-weight: 700; }
.shell__skip:focus { top: var(--space-4); }

.shell__rail {
    position: sticky;
    top: 0;
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
    height: 100vh;
    overflow-y: auto;
    padding: var(--space-5) var(--space-3);
    background: var(--color-rail);
}

.shell__brand { padding: 0 var(--space-3); color: var(--lamp); font-family: var(--font-display); font-size: 2rem; line-height: 1; text-decoration: none; }
.shell__links { display: flex; flex-direction: column; gap: 0.125rem; margin: 0; padding: 0; list-style: none; }
.shell__links--secondary { margin-top: auto; }

.shell__link {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    min-height: 2.5rem;
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-control);
    color: var(--color-muted);
    font-weight: 700;
    text-decoration: none;
    transition: color var(--transition), background var(--transition);
}

.shell__link:hover { color: var(--color-text); background: var(--color-hover); }
.shell__link--active { color: var(--color-text); background: var(--color-surface); }
.shell__link--active svg { color: var(--lamp); }

.shell__link--project { --project: var(--color-line-strong); font-weight: 400; }
.shell__link--project::before { content: ""; flex-shrink: 0; width: 0.625rem; height: 0.625rem; margin: 0 0.25rem; border-radius: 50%; background: var(--project); }
.shell__project-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.shell__count { color: var(--color-subtle); font-size: var(--font-size-sm); }

.shell__projects { display: flex; flex-direction: column; gap: var(--space-1); }
.shell__projects-header { display: flex; align-items: center; justify-content: space-between; padding-left: var(--space-3); }
.shell__projects-title { color: var(--color-subtle); font-size: var(--font-size-sm); }
.shell__hint { padding: 0 var(--space-3); color: var(--color-subtle); font-size: var(--font-size-sm); }

.shell__main { display: flex; flex-direction: column; gap: var(--space-5); min-width: 0; padding: var(--space-6) var(--space-6) var(--space-7); }
.shell__main :deep(.page) { display: flex; flex-direction: column; gap: var(--space-5); width: 100%; max-width: 46rem; }
.shell__main :deep(.page--wide) { max-width: none; }

.shell__strip { display: none; }

.shell__desk {
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    padding: var(--space-6) var(--space-5);
    background: var(--lamp-glow);
}

.shell__tabs { display: none; }

@media (max-width: 72rem) {
    .shell { grid-template-columns: var(--rail-width) minmax(0, 1fr); }
    .shell__desk { display: none; }
    .shell__strip { display: block; }
}

@media (max-width: 48rem) {
    .shell { grid-template-columns: minmax(0, 1fr); }
    .shell__rail { display: none; }
    .shell__main { gap: var(--space-4); padding: var(--space-4) var(--space-4) calc(6rem + env(safe-area-inset-bottom)); }
    .shell__main :deep(h1) { font-size: 1.875rem; }

    .shell__tabs {
        position: fixed;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 40;
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        padding: var(--space-1) var(--space-1) calc(var(--space-1) + env(safe-area-inset-bottom));
        border-top: 0.0625rem solid var(--color-line);
        background: color-mix(in oklch, var(--night-deep) 92%, transparent);
        backdrop-filter: blur(0.75rem);
    }

    .shell__tab { display: flex; flex-direction: column; align-items: center; gap: 0.125rem; padding: var(--space-2) 0; border-radius: var(--radius-control); color: var(--color-subtle); font-size: var(--font-size-xs); font-weight: 700; text-decoration: none; }
    .shell__tab--active { color: var(--lamp); }
}
</style>
