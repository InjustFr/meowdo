<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Trash2 } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import BaseButton from '../ui/BaseButton.vue';
import BaseDatePicker from '../ui/BaseDatePicker.vue';
import BaseModal from '../ui/BaseModal.vue';
import BaseSelect from '../ui/BaseSelect.vue';
import ConfirmButton from '../ui/ConfirmButton.vue';
import FormField from '../ui/FormField.vue';
import QuadrantPicker from './QuadrantPicker.vue';
import { ApiError, useApi } from '../../composables/useApi.js';
import { useProjects } from '../../composables/useProjects.js';
import { useTaskActions } from '../../composables/useTaskActions.js';
import { useToast } from '../../composables/useToast.js';

const props = defineProps({
    task: { type: Object, default: null },
});
const open = defineModel('open', { type: Boolean, required: true });

const { t } = useI18n();
const api = useApi();
const toast = useToast();
const actions = useTaskActions();
const { projects } = useProjects();

const form = reactive({ title: '', notes: '', projectId: null, dueOn: null, quadrant: null });
const errors = ref({});
const saving = ref(false);

watch(() => [open.value, props.task], () => {
    if (!open.value || !props.task) return;
    Object.assign(form, {
        title: props.task.title,
        notes: props.task.notes ?? '',
        projectId: props.task.projectId,
        dueOn: props.task.dueOn,
        quadrant: props.task.quadrant,
    });
    errors.value = {};
}, { immediate: true });

const projectOptions = computed(() => [
    { value: null, label: t('projects.none') },
    ...projects.value.map((project) => ({ value: project.id, label: project.name, color: project.color })),
]);

async function save() {
    saving.value = true;
    errors.value = {};
    try {
        await api.patch(`/api/tasks/${props.task.id}`, { title: form.title, notes: form.notes, projectId: form.projectId, dueOn: form.dueOn });
        if (form.quadrant !== props.task.quadrant) await api.post(`/api/tasks/${props.task.id}/classify`, { quadrant: form.quadrant });
        open.value = false;
    } catch (error) {
        if (error instanceof ApiError && error.violations.length) errors.value = error.fieldErrors;
        else toast.error(error.message);
    } finally {
        saving.value = false;
    }
}

async function remove() {
    await actions.remove(props.task);
    open.value = false;
}
</script>

<template>
    <BaseModal v-model:open="open" :title="t('tasks.editor.title')">
        <form v-if="task" class="task-editor" @submit.prevent="save">
            <FormField :label="t('tasks.editor.name')" :error="errors.title">
                <input v-model="form.title" type="text" maxlength="200" required>
            </FormField>
            <FormField :label="t('tasks.editor.notes')">
                <textarea v-model="form.notes" rows="3" />
            </FormField>
            <div class="task-editor__pair">
                <FormField :label="t('tasks.editor.project')" as="div">
                    <BaseSelect v-model="form.projectId" :options="projectOptions" />
                </FormField>
                <FormField :label="t('tasks.editor.deadline')" as="div" :error="errors.dueOn">
                    <BaseDatePicker v-model="form.dueOn" />
                </FormField>
            </div>
            <FormField :label="t('tasks.editor.matrix')" as="div">
                <QuadrantPicker v-model="form.quadrant" />
            </FormField>
            <div class="task-editor__actions">
                <ConfirmButton :icon="Trash2" :label="t('tasks.editor.delete')" :message="t('tasks.editor.deleteMessage', { title: task.title })" @confirm="remove" />
                <span class="task-editor__spacer" />
                <BaseButton variant="ghost" @click="open = false">{{ t('common.cancel') }}</BaseButton>
                <BaseButton type="submit" :loading="saving">{{ t('common.save') }}</BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.task-editor { display: flex; flex-direction: column; gap: var(--space-4); }
.task-editor__pair { display: grid; grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr)); gap: var(--space-4); }
.task-editor__actions { display: flex; align-items: center; gap: var(--space-2); }
.task-editor__spacer { flex: 1; }
</style>
