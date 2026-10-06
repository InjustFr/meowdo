import { computed, ref } from 'vue';
import { useApi } from './useApi.js';

const projects = ref([]);

export function useProjects() {
    const api = useApi();

    return {
        projects,
        byId: computed(() => new Map(projects.value.map((project) => [project.id, project]))),
        load: () => api.load('/api/projects', projects),
        create: (payload) => api.post('/api/projects', payload),
        edit: (id, payload) => api.patch(`/api/projects/${id}`, payload),
        remove: (id) => api.del(`/api/projects/${id}`),
    };
}
