import { getCurrentScope, onScopeDispose, ref } from 'vue';

export function useNow(every = 1000) {
    const now = ref(Date.now());
    const timer = window.setInterval(() => { now.value = Date.now(); }, every);
    if (getCurrentScope()) onScopeDispose(() => window.clearInterval(timer));
    return now;
}
