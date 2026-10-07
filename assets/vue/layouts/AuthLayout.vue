<script setup>
import CritterSvg from '../components/critter/CritterSvg.vue';

defineProps({
    title: { type: String, required: true },
    mood: { type: String, default: 'dormant' },
});
</script>

<template>
    <main class="auth-layout">
        <div class="auth-layout__panel">
            <p class="auth-layout__brand">mossydew</p>
            <section class="auth-layout__card">
                <div class="auth-layout__critter"><CritterSvg :mood="mood" /></div>
                <div class="auth-layout__body">
                    <h1 class="auth-layout__title">{{ title }}</h1>
                    <slot />
                </div>
            </section>
            <p v-if="$slots.footer" class="auth-layout__footer"><slot name="footer" /></p>
        </div>
    </main>
</template>

<style scoped>
.auth-layout { display: grid; place-items: center; min-height: 100vh; padding: var(--space-5) var(--space-4); }
.auth-layout__panel { width: 100%; max-width: 24rem; }
.auth-layout__brand { margin-bottom: var(--space-5); color: var(--color-ink); font-family: var(--font-display); font-size: 2rem; line-height: 1; text-align: center; }
.auth-layout__card { overflow: hidden; border: 0.0625rem solid var(--color-border); border-radius: var(--radius); background: var(--color-surface); }
.auth-layout__critter { border-bottom: 0.0625rem solid var(--color-border); }
.auth-layout__body { display: flex; flex-direction: column; gap: var(--space-4); padding: var(--space-6); }
.auth-layout__title { font-size: 1.5rem; }
.auth-layout__footer { margin-top: var(--space-4); font-size: var(--font-size-md); text-align: center; }
</style>
