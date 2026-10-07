import { reactive } from 'vue';

const state = reactive({ parentId: null });

export function useSubtaskComposer() {
    return {
        state,
        open: (task) => { state.parentId = task.id; },
        close: () => { state.parentId = null; },
    };
}
