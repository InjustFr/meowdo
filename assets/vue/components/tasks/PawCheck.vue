<script setup>
defineProps({
    done: { type: Boolean, required: true },
    label: { type: String, required: true },
});
const emit = defineEmits(['toggle']);
</script>

<template>
    <button type="button" :class="['paw-check', { 'paw-check--done': done }]" role="checkbox" :aria-checked="done" :aria-label="label" @click="emit('toggle')">
        <svg viewBox="0 0 64 64" aria-hidden="true">
            <ellipse cx="32" cy="42" rx="13" ry="10" />
            <ellipse cx="16" cy="28" rx="5.5" ry="7" />
            <ellipse cx="26" cy="18" rx="5.5" ry="7" />
            <ellipse cx="38" cy="18" rx="5.5" ry="7" />
            <ellipse cx="48" cy="28" rx="5.5" ry="7" />
        </svg>
    </button>
</template>

<style scoped>
.paw-check {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 1.75rem;
    height: 1.75rem;
    padding: 0.25rem;
    border: 0.125rem solid var(--color-line-strong);
    border-radius: 50%;
    background: transparent;
    cursor: pointer;
    transition: border-color var(--transition), background var(--transition);
}

.paw-check svg { width: 100%; height: 100%; fill: transparent; transition: fill var(--transition), transform var(--transition); }
.paw-check:hover { border-color: var(--catnip); }
.paw-check:hover svg { fill: color-mix(in oklch, var(--catnip) 40%, transparent); }
.paw-check--done { border-color: var(--catnip); background: var(--catnip); }
.paw-check--done svg { fill: var(--night); animation: stamp 260ms cubic-bezier(0.3, 1.6, 0.5, 1); }

@keyframes stamp { from { transform: scale(1.8) rotate(-18deg); opacity: 0; } }
</style>
