<script setup>
import { useI18n } from 'vue-i18n';
import BaseButton from '../components/ui/BaseButton.vue';
import EmptyState from '../components/ui/EmptyState.vue';

const { t } = useI18n();
</script>

<template>
    <div class="page">
        <EmptyState :title="t('notFound.title')" :hint="t('notFound.hint')">
            <BaseButton href="/">{{ t('notFound.back') }}</BaseButton>
        </EmptyState>
    </div>
</template>
