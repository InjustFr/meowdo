<script setup>
defineProps({
    variant: { type: String, default: 'error' },
});
</script>

<template>
    <p :class="['auth-message', `auth-message--${variant}`]" :role="variant === 'error' ? 'alert' : 'status'"><slot /></p>
</template>

<style scoped>
.auth-message { padding: var(--space-3); border-radius: var(--radius-control); font-size: var(--font-size-md); }
.auth-message--error { background: var(--color-danger-soft); color: var(--coral); }
.auth-message--success { background: var(--color-success-soft); color: var(--catnip); }
</style>
