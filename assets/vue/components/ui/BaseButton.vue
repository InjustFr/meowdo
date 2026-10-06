<script setup>
defineProps({
    variant: { type: String, default: 'primary' },
    type: { type: String, default: 'button' },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    href: { type: String, default: null },
});
</script>

<template>
    <a v-if="href" :href="href" :class="['button', `button--${variant}`]"><slot /></a>
    <button v-else :type="type" :class="['button', `button--${variant}`]" :disabled="disabled || loading" :aria-busy="loading || undefined">
        <span v-if="loading" class="button__spinner" aria-hidden="true" />
        <slot />
    </button>
</template>

<style scoped>
.button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    min-height: 2.75rem;
    padding: var(--space-2) var(--space-5);
    border: 0.0625rem solid transparent;
    border-radius: var(--radius-pill);
    font-weight: 700;
    font-size: var(--font-size-md);
    text-decoration: none;
    cursor: pointer;
    transition: background var(--transition), border-color var(--transition), color var(--transition), transform var(--transition);
}

.button:active:not(:disabled) { transform: translateY(0.0625rem); }
.button:disabled { opacity: 0.45; cursor: not-allowed; }

.button--primary { background: var(--color-accent); color: var(--color-on-accent); }
.button--primary:hover:not(:disabled) { background: var(--color-accent-strong); }

.button--secondary { background: transparent; border-color: var(--color-line-strong); color: var(--color-text); }
.button--secondary:hover:not(:disabled) { border-color: var(--color-text); }

.button--danger { background: transparent; border-color: var(--color-danger); color: var(--color-danger); }
.button--danger:hover:not(:disabled) { background: var(--color-danger-soft); }

.button--ghost { min-height: 2.25rem; padding: var(--space-1) var(--space-3); background: none; color: var(--color-muted); font-weight: 600; }
.button--ghost:hover:not(:disabled) { color: var(--color-text); background: var(--color-hover); }

.button__spinner { width: 0.875rem; height: 0.875rem; border: 0.125rem solid currentColor; border-right-color: transparent; border-radius: 50%; animation: button-spin 0.7s linear infinite; }

@keyframes button-spin { to { transform: rotate(360deg); } }
</style>
