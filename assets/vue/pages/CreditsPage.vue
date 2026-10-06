<script setup>
import { useI18n } from 'vue-i18n';
import PageHeader from '../components/ui/PageHeader.vue';

const { t } = useI18n();

const CREDITS = [
    { name: 'Bagel Fat One', author: 'The Bagel Fat Project Authors', license: 'SIL Open Font License 1.1', url: 'https://github.com/JAMO-TYPEFACE/BagelFat' },
    { name: 'Atkinson Hyperlegible Next', author: 'The Atkinson Hyperlegible Next Project Authors', license: 'SIL Open Font License 1.1', url: 'https://github.com/googlefonts/atkinson-hyperlegible-next' },
    { name: 'Lucide icons', author: 'Lucide contributors', license: 'ISC', url: 'https://lucide.dev' },
    { name: 'Reka UI', author: 'Reka UI contributors', license: 'MIT', url: 'https://reka-ui.com' },
    { name: 'Vue, Vue Router, Vue I18n', author: 'Vue contributors, intlify contributors', license: 'MIT', url: 'https://vuejs.org' },
    { name: 'Vue Draggable Plus (SortableJS)', author: 'Alfred-Skyblue, SortableJS contributors', license: 'MIT', url: 'https://github.com/Alfred-Skyblue/vue-draggable-plus' },
];
</script>

<template>
    <div class="page">
        <PageHeader :title="t('credits.title')" :subtitle="t('credits.subtitle')" />
        <p class="credits__cat">{{ t('credits.cat') }}</p>
        <ul class="credits">
            <li v-for="credit in CREDITS" :key="credit.name" class="credits__item">
                <a :href="credit.url" rel="noopener" target="_blank">{{ credit.name }}</a>
                <span>{{ credit.author }} · {{ credit.license }}</span>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.credits__cat { max-width: 40rem; color: var(--color-muted); }
.credits { display: flex; flex-direction: column; gap: var(--space-3); margin: 0; padding: 0; list-style: none; }
.credits__item { display: flex; flex-direction: column; }
.credits__item span { color: var(--color-muted); font-size: var(--font-size-sm); }
</style>
