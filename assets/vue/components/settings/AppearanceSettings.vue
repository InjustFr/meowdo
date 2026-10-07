<script setup>
import { computed, ref } from 'vue';
import { RadioGroupItem, RadioGroupRoot } from 'reka-ui';
import { useI18n } from 'vue-i18n';
import { useToast } from '../../composables/useToast.js';
import { THEMES, applyTheme, presetOf, themeNamed, useTheme } from '../../composables/useTheme.js';

const { t } = useI18n();
const toast = useToast();
const { saved, choose } = useTheme();

const kept = ref({ ...saved });
const preset = computed(() => presetOf(kept.value));

async function pick(name) {
    const next = themeNamed(name);
    if (!next || preset.value === name) return;
    const previous = { ...kept.value };
    kept.value = { background: next.background, accent: next.accent };
    if (!await toast.attempt(() => choose(next), t('settings.appearance.saved'))) {
        kept.value = previous;
        applyTheme(previous);
    }
}
</script>

<template>
    <RadioGroupRoot :model-value="preset" class="appearance" :aria-label="t('settings.appearance.themesLabel')" @update:model-value="pick">
        <RadioGroupItem v-for="option in THEMES" :key="option.name" :value="option.name" class="appearance__theme">
            <span class="appearance__sample" :style="{ background: option.background }" aria-hidden="true">
                <span class="appearance__sample-accent" :style="{ background: option.accent }" />
            </span>
            <span class="appearance__name">{{ t(`settings.appearance.themes.${option.name}`) }}</span>
        </RadioGroupItem>
    </RadioGroupRoot>
</template>

<style scoped>
.appearance { display: grid; grid-template-columns: repeat(auto-fill, minmax(7.5rem, 1fr)); gap: var(--space-3); }

.appearance__theme {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    padding: var(--space-2);
    border: 0.0625rem solid var(--color-border);
    border-radius: var(--radius);
    background: var(--color-surface);
    color: inherit;
    font: inherit;
    text-align: left;
    cursor: pointer;
    transition: border-color var(--transition), background var(--transition);
}

.appearance__theme:hover { border-color: var(--color-border-strong); }
.appearance__theme[data-state='checked'] { border-color: var(--color-accent); background: var(--color-accent-soft); }

.appearance__sample { display: flex; align-items: flex-end; height: 3rem; padding: var(--space-2); border: 0.0625rem solid var(--color-border); border-radius: var(--radius-inner); }
.appearance__sample-accent { width: 40%; height: 0.5rem; border-radius: var(--radius-sm); }
.appearance__name { color: var(--color-ink); font-size: var(--font-size-md); font-weight: 500; }
</style>
