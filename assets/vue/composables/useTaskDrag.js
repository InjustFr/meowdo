import { reactive } from 'vue';

const state = reactive({ task: null });

export function useTaskDrag() {
    return {
        state,
        start: (task) => { state.task = task; },
        end: () => { state.task = null; },
    };
}
