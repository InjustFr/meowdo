<script setup>
defineProps({
    done: { type: Boolean, required: true },
    label: { type: String, required: true },
    disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['toggle']);
</script>

<template>
    <button type="button" :class="['drop-check', { 'drop-check--done': done }]" role="checkbox" :aria-checked="done" :aria-label="label" :disabled="disabled" @click="emit('toggle')">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path class="drop-check__drop" d="M12 3.5q6 6.8 6 10.5a6 6 0 0 1-12 0q0-3.7 6-10.5z" />
            <path class="drop-check__tick" d="M9 14.2l2.2 2.2 4-4.4" />
        </svg>
    </button>
</template>

<style scoped>
.drop-check {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 1.625rem;
    height: 1.625rem;
    padding: 0;
    border: 0.09375rem solid var(--color-border-strong);
    border-radius: 50%;
    background: var(--color-surface);
    cursor: pointer;
    transition: border-color var(--transition), background var(--transition);
}

.drop-check svg { width: 1.125rem; height: 1.125rem; overflow: visible; }
.drop-check__drop { fill: transparent; transform-box: fill-box; transform-origin: 50% 100%; transform: scale(0.4); opacity: 0; transition: fill var(--transition), transform var(--transition), opacity var(--transition); }
.drop-check__tick { fill: none; stroke: var(--color-on-accent); stroke-width: 2.25; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 10; stroke-dashoffset: 10; }

.drop-check:hover { border-color: var(--color-accent); }
.drop-check:hover .drop-check__drop { fill: var(--color-accent-soft); opacity: 1; transform: scale(0.7); }

.drop-check--done { border-color: var(--color-accent); background: var(--color-accent); }
.drop-check--done .drop-check__drop { fill: transparent; opacity: 0; }
.drop-check--done .drop-check__tick { animation: tick 240ms ease-out 80ms forwards; }
.drop-check--done:hover .drop-check__drop { opacity: 0; }

.drop-check:disabled { border-style: dashed; cursor: not-allowed; }
.drop-check:disabled:hover { border-color: var(--color-border-strong); }
.drop-check:disabled .drop-check__drop { opacity: 0; }

@keyframes tick { to { stroke-dashoffset: 0; } }
</style>
