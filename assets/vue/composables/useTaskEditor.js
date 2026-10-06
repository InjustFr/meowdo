import { reactive } from 'vue';

const state = reactive({ open: false, task: null });

export function useTaskEditor() {
    return {
        state,
        edit(task) {
            state.task = task;
            state.open = true;
        },
    };
}
