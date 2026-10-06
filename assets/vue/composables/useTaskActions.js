import { useI18n } from 'vue-i18n';
import { useApi } from './useApi.js';
import { useCelebration } from './useCelebration.js';
import { useToast } from './useToast.js';

export function useTaskActions() {
    const api = useApi();
    const toast = useToast();
    const celebration = useCelebration();
    const { t } = useI18n();

    async function attempt(action) {
        try {
            return await action();
        } catch (error) {
            toast.error(error.message);
            return null;
        }
    }

    return {
        create: (payload) => attempt(() => api.post('/api/tasks', payload)),
        edit: (task, payload) => attempt(() => api.patch(`/api/tasks/${task.id}`, payload)),
        plan: (task, when, date = null) => attempt(() => api.post(`/api/tasks/${task.id}/plan`, { when, date })),
        classify: (task, quadrant) => attempt(() => api.post(`/api/tasks/${task.id}/classify`, { quadrant })),
        reorder: (quadrant, ids) => attempt(() => api.put(`/api/matrix/${quadrant}/order`, { ids })),
        moveOverdueToToday: () => attempt(() => api.post('/api/tasks/overdue/plan-today')),
        async complete(task) {
            task.done = true;
            const completion = await attempt(() => api.post(`/api/tasks/${task.id}/complete`));
            if (completion === null) {
                task.done = false;
                return null;
            }
            celebration.reward(completion);
            return completion;
        },
        async reopen(task) {
            task.done = false;
            const reopened = await attempt(() => api.post(`/api/tasks/${task.id}/reopen`));
            if (reopened === null) task.done = true;
            return reopened;
        },
        async remove(task) {
            try {
                await api.del(`/api/tasks/${task.id}`);
                toast.success(t('tasks.deleted', { title: task.title }));
            } catch (error) {
                toast.error(error.message);
            }
        },
    };
}
