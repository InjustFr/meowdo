<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    player: { type: Object, required: true },
    progress: { type: Number, required: true },
});

const { t } = useI18n();
const remaining = computed(() => props.player.nextLevelXp - props.player.xp);
</script>

<template>
    <div class="xp-bar">
        <span class="xp-bar__level" :aria-label="t('herbarium.level', { level: player.level })">{{ player.level }}</span>
        <div class="xp-bar__body">
            <div
                class="xp-bar__track"
                role="progressbar"
                :aria-label="t('herbarium.xpProgress')"
                :aria-valuenow="player.xp - player.levelStartXp"
                :aria-valuemin="0"
                :aria-valuemax="player.nextLevelXp - player.levelStartXp"
            >
                <span class="xp-bar__fill" :style="{ width: `${progress * 100}%` }" />
            </div>
            <span class="xp-bar__caption tabular">{{ t('herbarium.toNextLevel', { xp: remaining, level: player.level + 1 }) }}</span>
        </div>
    </div>
</template>

<style scoped>
.xp-bar { display: flex; align-items: center; gap: var(--space-3); }
.xp-bar__level {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: var(--color-accent);
    color: var(--color-on-accent);
    font-family: var(--font-display);
    font-size: 1.25rem;
    line-height: 1;
}
.xp-bar__body { display: flex; flex: 1; flex-direction: column; gap: var(--space-1); min-width: 0; }
.xp-bar__track { height: 0.5rem; overflow: hidden; border-radius: var(--radius-pill); background: var(--color-border); }
.xp-bar__fill { display: block; height: 100%; border-radius: inherit; background: var(--color-accent); transition: width 600ms cubic-bezier(0.2, 0.8, 0.2, 1); }
.xp-bar__caption { color: var(--color-muted); font-size: var(--font-size-xs); }
</style>
