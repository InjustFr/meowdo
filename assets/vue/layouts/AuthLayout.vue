<script setup>
import CatSvg from '../components/cat/CatSvg.vue';

defineProps({
    title: { type: String, required: true },
    mood: { type: String, default: 'sleepy' },
});
</script>

<template>
    <main class="auth-layout">
        <div class="auth-layout__panel">
            <p class="auth-layout__brand">Meowdo</p>
            <div class="auth-layout__cat"><CatSvg coat="ginger" :mood="mood" /></div>
            <section class="auth-layout__card">
                <h1 class="auth-layout__title">{{ title }}</h1>
                <slot />
            </section>
            <p v-if="$slots.footer" class="auth-layout__footer"><slot name="footer" /></p>
        </div>
    </main>
</template>

<style scoped>
.auth-layout { display: grid; place-items: center; min-height: 100vh; padding: var(--space-5) var(--space-4); background: var(--lamp-glow), var(--color-bg); }
.auth-layout__panel { width: 100%; max-width: 24rem; }
.auth-layout__brand { margin-bottom: var(--space-4); color: var(--lamp); font-family: var(--font-display); font-size: 2.75rem; line-height: 1; }
.auth-layout__cat { overflow: hidden; margin-bottom: calc(-1 * var(--space-5)); border-radius: var(--radius-panel) var(--radius-panel) 0 0; }
.auth-layout__card { position: relative; display: flex; flex-direction: column; gap: var(--space-4); padding: var(--space-5); border-radius: var(--radius-panel); background: var(--color-surface); box-shadow: var(--shadow); }
.auth-layout__title { font-size: 1.75rem; }
.auth-layout__footer { margin-top: var(--space-4); font-size: var(--font-size-md); }
</style>
