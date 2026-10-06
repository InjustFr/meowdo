<script setup>
defineProps({
    title: { type: String, required: true },
    hint: { type: String, default: null },
});
</script>

<template>
    <div class="empty-state">
        <svg class="empty-state__paw" viewBox="0 0 64 64" aria-hidden="true">
            <ellipse cx="32" cy="42" rx="14" ry="11" />
            <ellipse cx="15" cy="27" rx="6" ry="7.5" />
            <ellipse cx="26" cy="17" rx="6" ry="7.5" />
            <ellipse cx="38" cy="17" rx="6" ry="7.5" />
            <ellipse cx="49" cy="27" rx="6" ry="7.5" />
        </svg>
        <p class="empty-state__title">{{ title }}</p>
        <p v-if="hint" class="empty-state__hint">{{ hint }}</p>
        <slot />
    </div>
</template>

<style scoped>
.empty-state { display: flex; flex-direction: column; align-items: flex-start; gap: var(--space-1); padding: var(--space-5) 0; }
.empty-state__paw { width: 2.5rem; height: 2.5rem; margin-bottom: var(--space-2); fill: var(--color-line-strong); }
.empty-state__title { font-weight: 700; }
.empty-state__hint { color: var(--color-muted); font-size: var(--font-size-md); }
</style>
