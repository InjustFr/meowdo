<script setup>
import { TooltipArrow, TooltipContent, TooltipPortal, TooltipRoot, TooltipTrigger } from 'reka-ui';

defineOptions({ inheritAttrs: false });

defineProps({
    icon: { type: [Object, Function], required: true },
    label: { type: String, required: true },
    variant: { type: String, default: 'default' },
    pressed: { type: Boolean, default: undefined },
});
</script>

<template>
    <TooltipRoot>
        <TooltipTrigger as-child>
            <button type="button" :class="['icon-button', `icon-button--${variant}`]" :aria-label="label" :aria-pressed="pressed" v-bind="$attrs">
                <component :is="icon" size="1.125rem" :stroke-width="2" aria-hidden="true" />
            </button>
        </TooltipTrigger>
        <TooltipPortal>
            <TooltipContent class="icon-button__tooltip" side="top" :side-offset="4">
                {{ label }}
                <TooltipArrow class="icon-button__arrow" :width="8" :height="4" />
            </TooltipContent>
        </TooltipPortal>
    </TooltipRoot>
</template>

<style scoped>
.icon-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border: none;
    border-radius: var(--radius-pill);
    background: none;
    color: var(--color-muted);
    cursor: pointer;
    transition: color var(--transition), background var(--transition);
}

.icon-button:hover { color: var(--color-text); background: var(--color-hover); }
.icon-button[aria-pressed="true"] { color: var(--color-accent); }
.icon-button--danger:hover { color: var(--color-danger); background: var(--color-danger-soft); }
.icon-button:disabled { opacity: 0.35; pointer-events: none; }
</style>

<style>
.icon-button__tooltip { z-index: 70; padding: var(--space-1) var(--space-2); border-radius: 0.375rem; background: var(--moonmilk); color: var(--night); font-size: var(--font-size-sm); font-weight: 600; animation: fade-in var(--transition); }
.icon-button__arrow { fill: var(--moonmilk); }
</style>
