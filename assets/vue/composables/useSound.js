import { ref, watch } from 'vue';

const KEY = 'meowdo.sound';

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

function hit(at, pitch) {
    const oscillator = context.createOscillator();
    const gain = context.createGain();
    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(pitch, at);
    oscillator.frequency.exponentialRampToValueAtTime(pitch * 0.55, at + 0.16);
    gain.gain.setValueAtTime(0.0001, at);
    gain.gain.exponentialRampToValueAtTime(0.5, at + 0.005);
    gain.gain.exponentialRampToValueAtTime(0.0001, at + 0.22);
    oscillator.connect(gain).connect(context.destination);
    oscillator.start(at);
    oscillator.stop(at + 0.25);
}

export function useSound() {
    function bongo(hits = 4) {
        if (!enabled.value) return;
        context ??= new (window.AudioContext ?? window.webkitAudioContext)();
        const start = context.currentTime + 0.01;
        for (let index = 0; index < hits; index += 1) {
            hit(start + index * 0.22, index % 2 === 0 ? 220 : 300);
        }
    }

    return { enabled, bongo };
}
