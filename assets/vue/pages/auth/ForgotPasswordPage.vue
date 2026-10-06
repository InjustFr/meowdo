<script setup>
import { useI18n } from 'vue-i18n';
import AuthLayout from '../../layouts/AuthLayout.vue';
import AuthMessage from '../../components/auth/AuthMessage.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import FormField from '../../components/ui/FormField.vue';

defineProps({
    email: { type: String, default: '' },
    sent: { type: Boolean, default: false },
    error: { type: String, default: null },
    csrfToken: { type: String, required: true },
});

const { t } = useI18n();
</script>

<template>
    <AuthLayout :title="t('auth.forgotPassword.title')" mood="idle">
        <AuthMessage v-if="sent" variant="success">{{ t('auth.forgotPassword.sent', { email }) }}</AuthMessage>
        <template v-else>
            <p class="auth-intro">{{ t('auth.forgotPassword.intro') }}</p>
            <AuthMessage v-if="error">{{ error }}</AuthMessage>
            <form class="auth-form" method="post" action="/password/forgot">
                <input type="hidden" name="_csrf_token" :value="csrfToken">
                <FormField :label="t('auth.email')">
                    <input type="email" name="email" :value="email" autocomplete="email" required autofocus>
                </FormField>
                <BaseButton type="submit">{{ t('auth.forgotPassword.submit') }}</BaseButton>
            </form>
        </template>
        <template #footer>
            <a href="/login">{{ t('auth.backToLogin') }}</a>
        </template>
    </AuthLayout>
</template>

<style scoped>
.auth-intro { color: var(--color-muted); }
.auth-form { display: flex; flex-direction: column; gap: var(--space-4); }
</style>
