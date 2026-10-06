<script setup>
import { useI18n } from 'vue-i18n';
import AuthLayout from '../../layouts/AuthLayout.vue';
import AuthMessage from '../../components/auth/AuthMessage.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import FormField from '../../components/ui/FormField.vue';

defineProps({
    lastEmail: { type: String, default: '' },
    error: { type: String, default: null },
    notice: { type: String, default: null },
    csrfToken: { type: String, required: true },
});

const { t } = useI18n();
</script>

<template>
    <AuthLayout :title="t('auth.login.title')" :mood="notice ? 'purring' : 'sleepy'">
        <AuthMessage v-if="notice" variant="success">{{ notice }}</AuthMessage>
        <AuthMessage v-if="error">{{ error }}</AuthMessage>
        <form class="auth-form" method="post" action="/login">
            <input type="hidden" name="_csrf_token" :value="csrfToken">
            <FormField :label="t('auth.email')">
                <input type="email" name="email" :value="lastEmail" autocomplete="email" required autofocus>
            </FormField>
            <FormField :label="t('auth.password')">
                <input type="password" name="password" autocomplete="current-password" required>
            </FormField>
            <BaseButton type="submit">{{ t('auth.login.submit') }}</BaseButton>
        </form>
        <template #footer>
            <a href="/password/forgot">{{ t('auth.login.forgotPassword') }}</a>
        </template>
    </AuthLayout>
</template>

<style scoped>
.auth-form { display: flex; flex-direction: column; gap: var(--space-4); }
</style>
