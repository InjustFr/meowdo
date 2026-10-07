import { ref, watch } from 'vue';

const KEY = 'mossydew.sound';

function stored() {
    try {
        return window.localStorage.getItem(KEY) === 'on';
    } catch {
        return false;
    }
}

const enabled = ref(stored());
let context = null;

watch(enabled, (value) => {
    try {
        window.localStorage.setItem(KEY, value ? 'on' : 'off');
    } catch {
        enabled.value = value;
    }
});

function plip(at, pitch) {
    const oscillator = context.createOscillator();
    const gain = context.createGain();
    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(pitch, at);
    oscillator.frequency.exponentialRampToValueAtTime(pitch * 2.4, at + 0.08);
    gain.gain.setValueAtTime(0.0001, at);
    gain.gain.exponentialRampToValueAtTime(0.35, at + 0.01);
    gain.gain.exponentialRampToValueAtTime(0.0001, at + 0.18);
    oscillator.connect(gain).connect(context.destination);
    oscillator.start(at);
    oscillator.stop(at + 0.2);
}

export function useSound() {
    function drip() {
        if (!enabled.value) return;
        context ??= new (window.AudioContext ?? window.webkitAudioContext)();
        const start = context.currentTime + 0.4;
        plip(start, 520);
        plip(start + 0.14, 780);
    }

    return { enabled, drip };
}
