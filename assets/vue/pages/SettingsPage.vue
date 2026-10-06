<script setup>
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { SwitchRoot, SwitchThumb } from 'reka-ui';
import { useI18n } from 'vue-i18n';
import BaseButton from '../components/ui/BaseButton.vue';
import BaseSelect from '../components/ui/BaseSelect.vue';
import FormField from '../components/ui/FormField.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import PageSection from '../components/ui/PageSection.vue';
import { useApi } from '../composables/useApi.js';
import { useSession } from '../composables/useSession.js';
import { useSound } from '../composables/useSound.js';
import { useToast } from '../composables/useToast.js';
import { LANGUAGES, currentLanguage } from '../i18n/locale.js';

const { t } = useI18n();
const api = useApi();
const toast = useToast();
const session = useSession();
const { enabled: sound, bongo } = useSound();

const timezones = Intl.supportedValuesOf('timeZone').map((zone) => ({ value: zone, label: zone.replaceAll('_', ' ') }));
const browserZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
const timezone = ref(session?.timezone ?? browserZone);
const language = ref(currentLanguage());
const languages = computed(() => LANGUAGES.map((known) => ({ value: known.value, label: known.label })));

async function saveTimezone(value) {
    try {
        await api.put('/api/me/timezone', { timezone: value });
        timezone.value = value;
        toast.success(t('settings.timezoneSaved'));
    } catch (error) {
        toast.error(error.message);
    }
}

async function saveLanguage(value) {
    try {
        await api.put('/api/me/language', { language: value });
        window.location.reload();
    } catch (error) {
        toast.error(error.message);
    }
}

function toggleSound(value) {
    sound.value = value;
    if (value) bongo(2);
}
</script>

<template>
    <div class="page">
        <PageHeader :title="t('settings.title')" :subtitle="session?.email" />
        <PageSection :title="t('settings.preferences')">
            <div class="settings">
                <FormField :label="t('settings.timezone')" :hint="t('settings.timezoneHint')" as="div">
                    <BaseSelect :model-value="timezone" :options="timezones" @update:model-value="saveTimezone" />
                </FormField>
                <FormField :label="t('settings.language')" as="div">
                    <BaseSelect :model-value="language" :options="languages" @update:model-value="saveLanguage" />
                </FormField>
                <label class="settings__switch">
                    <SwitchRoot :model-value="sound" class="switch" @update:model-value="toggleSound"><SwitchThumb class="switch__thumb" /></SwitchRoot>
                    <span>{{ t('settings.sound') }}</span>
                </label>
            </div>
        </PageSection>
        <PageSection :title="t('settings.account')">
            <form method="post" action="/logout" class="settings__logout">
                <input type="hidden" name="_csrf_token" :value="session?.logoutToken">
                <BaseButton type="submit" variant="secondary">{{ t('settings.logout') }}</BaseButton>
            </form>
        </PageSection>
        <RouterLink to="/credits" class="settings__credits">{{ t('settings.credits') }}</RouterLink>
    </div>
</template>

<style scoped>
.settings { display: flex; flex-direction: column; gap: var(--space-4); max-width: 28rem; }
.settings__switch { display: flex; align-items: center; gap: var(--space-3); cursor: pointer; }
.switch { position: relative; flex-shrink: 0; width: 2.75rem; height: 1.625rem; padding: 0; border: none; border-radius: var(--radius-pill); background: var(--color-line-strong); cursor: pointer; transition: background var(--transition); }
.switch[data-state="checked"] { background: var(--catnip); }
.switch__thumb { display: block; width: 1.25rem; height: 1.25rem; border-radius: 50%; background: var(--moonmilk); transform: translateX(0.1875rem); transition: transform var(--transition); }
.switch__thumb[data-state="checked"] { transform: translateX(1.3125rem); }
.settings__credits { color: var(--color-muted); font-size: var(--font-size-sm); }
</style>
