<script setup>
import { computed } from 'vue';

const props = defineProps({
    coat: { type: String, default: 'ginger' },
    mood: { type: String, default: 'idle' },
    outfit: { type: Object, default: () => ({}) },
    slapping: { type: Boolean, default: false },
    label: { type: String, default: null },
});

const hat = computed(() => props.outfit.hat ?? null);
const neckwear = computed(() => props.outfit.neckwear ?? null);
const toy = computed(() => props.outfit.toy ?? null);
const backdrop = computed(() => props.outfit.backdrop ?? 'window');
const uid = `cat-${Math.random().toString(36).slice(2, 8)}`;
</script>

<template>
    <svg
        :class="['cat', `cat--coat-${coat}`, `cat--mood-${mood}`, { 'cat--slap': slapping }]"
        viewBox="0 0 260 200"
        :role="label ? 'img' : undefined"
        :aria-label="label ?? undefined"
        :aria-hidden="label ? undefined : 'true'"
    >
        <defs>
            <clipPath :id="`${uid}-scene`"><rect x="0" y="0" width="260" height="200" rx="18" /></clipPath>
            <radialGradient :id="`${uid}-lamp`" cx="0.78" cy="0" r="0.9">
                <stop offset="0" stop-color="#ffc857" stop-opacity="0.38" />
                <stop offset="1" stop-color="#ffc857" stop-opacity="0" />
            </radialGradient>
        </defs>

        <g :clip-path="`url(#${uid}-scene)`">
            <g class="cat__backdrop">
                <template v-if="backdrop === 'rooftop'">
                    <rect width="260" height="200" fill="#241f45" />
                    <circle cx="204" cy="38" r="16" fill="#f6efd8" />
                    <path d="M0 150V96h22v-14h18v30h16V88h26v62zM176 150v-48h20V84h22v18h16v-26h26v74z" fill="#141129" />
                    <g fill="#ffc857" opacity="0.8">
                        <rect x="8" y="104" width="5" height="6" /><rect x="28" y="96" width="5" height="6" /><rect x="64" y="98" width="5" height="6" />
                        <rect x="184" y="110" width="5" height="6" /><rect x="224" y="92" width="5" height="6" /><rect x="244" y="86" width="5" height="6" />
                    </g>
                </template>
                <template v-else-if="backdrop === 'library'">
                    <rect width="260" height="200" fill="#2b2140" />
                    <g v-for="(shelf, row) in [40, 92]" :key="row">
                        <rect x="0" :y="shelf + 34" width="260" height="6" fill="#4a3557" />
                        <rect v-for="(book, index) in 18" :key="index" :x="index * 15 + (row * 7) % 11" :y="shelf + (index % 3) * 3" width="11" :height="34 - (index % 3) * 3" :fill="['#ff8fa3', '#6ec5ff', '#7bd389', '#ffc857', '#a99ce0', '#f2994a'][(index + row) % 6]" opacity="0.75" rx="1.5" />
                    </g>
                </template>
                <template v-else-if="backdrop === 'aquarium'">
                    <rect width="260" height="200" fill="#123a5c" />
                    <path d="M0 26q32-10 65 0t65 0 65 0 65 0V0H0z" fill="#1c5a86" />
                    <g fill="none" stroke="#9fe0ff" stroke-width="2" opacity="0.7">
                        <circle cx="34" cy="70" r="5" /><circle cx="44" cy="48" r="3" /><circle cx="226" cy="84" r="4" /><circle cx="214" cy="56" r="6" />
                    </g>
                    <path d="M196 116q14-12 30 0-16 12-30 0l-10 8v-16z" fill="#ff8fa3" />
                    <path d="M40 112q10-9 22 0-12 9-22 0l-8 6v-12z" fill="#ffc857" />
                    <path d="M18 150q4-30 0-50M30 150q-6-24 2-44M232 150q6-28-2-46" stroke="#4fbf8f" stroke-width="4" fill="none" stroke-linecap="round" />
                </template>
                <template v-else-if="backdrop === 'sakura'">
                    <rect width="260" height="200" fill="#3a2550" />
                    <circle cx="56" cy="44" r="22" fill="#ffd8e2" opacity="0.25" />
                    <path d="M260 22q-60 6-96 40m48-30q-8 20-2 34" stroke="#5a3a3a" stroke-width="5" fill="none" stroke-linecap="round" />
                    <g fill="#ffb7c9">
                        <circle cx="168" cy="58" r="7" /><circle cx="186" cy="44" r="6" /><circle cx="206" cy="30" r="8" /><circle cx="214" cy="62" r="6" />
                        <circle cx="236" cy="26" r="7" /><circle cx="248" cy="44" r="5" />
                    </g>
                    <g fill="#ffd1dc" opacity="0.9">
                        <ellipse cx="40" cy="96" rx="4" ry="2.5" transform="rotate(30 40 96)" /><ellipse cx="80" cy="66" rx="4" ry="2.5" transform="rotate(-20 80 66)" />
                        <ellipse cx="226" cy="110" rx="4" ry="2.5" transform="rotate(40 226 110)" /><ellipse cx="22" cy="130" rx="4" ry="2.5" />
                    </g>
                </template>
                <template v-else>
                    <rect width="260" height="200" fill="#211d3d" />
                    <rect x="22" y="22" width="78" height="92" rx="6" fill="#141129" stroke="#3a3466" stroke-width="5" />
                    <path d="M61 22v92M22 68h78" stroke="#3a3466" stroke-width="4" />
                    <circle cx="80" cy="44" r="9" fill="#f6efd8" />
                    <circle cx="84" cy="41" r="8" fill="#141129" />
                    <g fill="#eee8f7"><circle cx="36" cy="38" r="1.4" /><circle cx="48" cy="88" r="1.2" /><circle cx="86" cy="96" r="1.4" /><circle cx="34" cy="100" r="1" /></g>
                </template>
                <rect width="260" height="200" :fill="`url(#${uid}-lamp)`" />
            </g>

            <g class="cat__body">
                <path class="cat__fur" d="M58 158C54 112 66 74 98 62L90 26l30 26c7-2 13-2 20 0l30-26-8 36c32 12 44 50 40 96z" />
                <path class="cat__inner-ear" d="M97 38l6 18 12-5zM163 38l-6 18-12-5z" />
                <g class="cat__markings">
                    <path class="cat__stripe" d="M120 62q10 6 20 0M116 72q14 7 28 0" />
                    <path class="cat__patch cat__patch--left" d="M90 26l30 26c-12 0-22 6-28 14-2-12-3-26-2-40z" />
                    <path class="cat__patch cat__patch--right" d="M170 26l-8 36c8 4 14 10 18 18 4-20-2-40-10-54z" />
                    <ellipse class="cat__muzzle" cx="130" cy="106" rx="24" ry="17" />
                </g>
                <g class="cat__face">
                    <g class="cat__eyes cat__eyes--open">
                        <circle cx="111" cy="92" r="5" /><circle cx="149" cy="92" r="5" />
                        <circle class="cat__glint" cx="113" cy="90" r="1.6" /><circle class="cat__glint" cx="151" cy="90" r="1.6" />
                    </g>
                    <path class="cat__eyes cat__eyes--happy" d="M104 95q7-9 14 0M142 95q7-9 14 0" />
                    <path class="cat__eyes cat__eyes--asleep" d="M104 93q7 5 14 0M142 93q7 5 14 0" />
                    <path class="cat__nose" d="M126 100h8l-4 4z" />
                    <path class="cat__mouth" d="M122 107q4 5 8 0q4 5 8 0" />
                    <ellipse class="cat__blush" cx="99" cy="104" rx="7" ry="4" /><ellipse class="cat__blush" cx="161" cy="104" rx="7" ry="4" />
                    <path class="cat__whiskers" d="M84 100l-18-3M84 106l-18 3M176 100l18-3M176 106l18 3" />
                </g>
            </g>

            <g class="cat__neckwear">
                <g v-if="neckwear === 'bell-collar'">
                    <path d="M78 124q52 18 104 0" class="cat__band" stroke="#e5484d" />
                    <circle cx="130" cy="134" r="7" fill="#ffc857" stroke="#3a2340" stroke-width="2.5" />
                    <path d="M126 135h8" stroke="#3a2340" stroke-width="2" />
                </g>
                <g v-else-if="neckwear === 'bow-tie'">
                    <path d="M130 128l-18-10v20zM130 128l18-10v20z" fill="#6ec5ff" stroke="#3a2340" stroke-width="2.5" stroke-linejoin="round" />
                    <circle cx="130" cy="128" r="5" fill="#4a9fd8" stroke="#3a2340" stroke-width="2.5" />
                </g>
                <g v-else-if="neckwear === 'bandana'">
                    <path d="M80 122q50 16 100 0l-50 30z" fill="#ff6b6b" stroke="#3a2340" stroke-width="2.5" stroke-linejoin="round" />
                    <g fill="#fff3f3"><circle cx="112" cy="130" r="2" /><circle cx="130" cy="140" r="2" /><circle cx="148" cy="130" r="2" /></g>
                </g>
                <g v-else-if="neckwear === 'scarf'">
                    <path d="M76 120q54 20 108 0" class="cat__band cat__band--thick" stroke="#7bd389" />
                    <path d="M150 128l8 26h-14z" fill="#7bd389" stroke="#3a2340" stroke-width="2.5" stroke-linejoin="round" />
                    <path d="M92 124l-4 6M108 128l-3 7M124 130v7M140 130l2 7M164 126l4 6" stroke="#4fae64" stroke-width="2.5" />
                </g>
            </g>

            <g class="cat__hat">
                <g v-if="hat === 'party-hat'">
                    <path d="M112 56l18-46 18 46z" fill="#ff8fa3" stroke="#3a2340" stroke-width="3" stroke-linejoin="round" />
                    <path d="M118 42l18-6M115 50l26-8M124 28l9-3" stroke="#ffc857" stroke-width="3" stroke-linecap="round" />
                    <circle cx="130" cy="10" r="6" fill="#ffc857" stroke="#3a2340" stroke-width="2.5" />
                </g>
                <g v-else-if="hat === 'beanie'">
                    <path d="M100 64q30-48 60 0z" fill="#6ec5ff" stroke="#3a2340" stroke-width="3" stroke-linejoin="round" />
                    <rect x="98" y="56" width="64" height="10" rx="4" fill="#4a9fd8" stroke="#3a2340" stroke-width="3" />
                    <circle cx="130" cy="24" r="7" fill="#eee8f7" stroke="#3a2340" stroke-width="2.5" />
                </g>
                <g v-else-if="hat === 'frog-hat'">
                    <path d="M96 66q34-56 68 0z" fill="#7bd389" stroke="#3a2340" stroke-width="3" stroke-linejoin="round" />
                    <circle cx="114" cy="32" r="9" fill="#7bd389" stroke="#3a2340" stroke-width="3" />
                    <circle cx="146" cy="32" r="9" fill="#7bd389" stroke="#3a2340" stroke-width="3" />
                    <circle cx="114" cy="32" r="4" fill="#3a2340" /><circle cx="146" cy="32" r="4" fill="#3a2340" />
                    <path d="M118 52q12 8 24 0" stroke="#3a2340" stroke-width="2.5" fill="none" stroke-linecap="round" />
                </g>
                <g v-else-if="hat === 'wizard-hat'">
                    <ellipse cx="130" cy="58" rx="40" ry="8" fill="#5b4a9e" stroke="#3a2340" stroke-width="3" />
                    <path d="M108 58q10-34 34-56 2 30 10 56z" fill="#6f5cc2" stroke="#3a2340" stroke-width="3" stroke-linejoin="round" />
                    <path d="M130 30l2 5 5 1-4 3 1 5-4-3-4 3 1-5-4-3 5-1z" fill="#ffc857" />
                    <circle cx="124" cy="48" r="2" fill="#ffc857" /><circle cx="140" cy="42" r="1.6" fill="#ffc857" />
                </g>
                <g v-else-if="hat === 'crown'">
                    <path d="M106 60l-4-30 14 14 14-22 14 22 14-14-4 30z" fill="#ffc857" stroke="#3a2340" stroke-width="3" stroke-linejoin="round" />
                    <circle cx="130" cy="48" r="4" fill="#ff6b6b" /><circle cx="114" cy="52" r="3" fill="#6ec5ff" /><circle cx="146" cy="52" r="3" fill="#7bd389" />
                </g>
            </g>

            <g class="cat__mood-mark">
                <g v-if="mood === 'sleepy'" class="cat__zzz">
                    <text x="182" y="58">z</text><text x="194" y="44" class="cat__zzz--small">z</text>
                </g>
                <g v-else-if="mood === 'purring'" class="cat__purr">
                    <path d="M196 84q6-6 12 0M200 96q8-6 16 0M56 84q-6-6-12 0M52 96q-8-6-16 0" />
                </g>
            </g>

            <g class="cat__desk">
                <rect x="0" y="152" width="260" height="48" fill="#3b2f52" />
                <rect x="0" y="150" width="260" height="5" fill="#53436f" />
            </g>

            <g class="cat__toy">
                <g v-if="toy === 'yarn-ball'" transform="translate(224 158)">
                    <circle r="13" fill="#ff8fa3" stroke="#3a2340" stroke-width="2.5" />
                    <path d="M-10-5q10 4 20-3M-11 3q11 5 22-2M-6-11q4 11 2 22" stroke="#c9566e" stroke-width="2" fill="none" />
                    <path d="M12 6q12 10 22 6" stroke="#ff8fa3" stroke-width="2.5" fill="none" />
                </g>
                <g v-else-if="toy === 'fish'" transform="translate(26 162)">
                    <path d="M0 0q14-12 28 0-14 12-28 0l-10-8v16z" fill="#6ec5ff" stroke="#3a2340" stroke-width="2.5" stroke-linejoin="round" />
                    <circle cx="20" cy="-2" r="2" fill="#3a2340" />
                </g>
                <g v-else-if="toy === 'mouse'" transform="translate(222 162)">
                    <ellipse rx="14" ry="9" fill="#b9b0d0" stroke="#3a2340" stroke-width="2.5" />
                    <circle cx="-8" cy="-9" r="5" fill="#b9b0d0" stroke="#3a2340" stroke-width="2.5" />
                    <circle cx="-12" cy="-1" r="1.6" fill="#3a2340" />
                    <path d="M14 2q12 2 14 12" stroke="#ff8fa3" stroke-width="2.5" fill="none" stroke-linecap="round" />
                </g>
                <g v-else-if="toy === 'laser-dot'" transform="translate(34 166)">
                    <g class="cat__laser">
                        <circle r="10" fill="#ff3b3b" opacity="0.25" />
                        <circle r="4" fill="#ff5050" />
                    </g>
                </g>
            </g>

            <g class="cat__bongos">
                <g class="cat__bongo cat__bongo--left">
                    <path d="M66 156l6 32h46l6-32z" fill="#c4623a" stroke="#3a2340" stroke-width="3" stroke-linejoin="round" />
                    <path d="M69 170h52" stroke="#8c3f22" stroke-width="3" />
                    <ellipse cx="95" cy="156" rx="30" ry="8" fill="#f3e3c3" stroke="#3a2340" stroke-width="3" />
                </g>
                <g class="cat__bongo cat__bongo--right">
                    <path d="M138 158l5 30h38l5-30z" fill="#d9774a" stroke="#3a2340" stroke-width="3" stroke-linejoin="round" />
                    <path d="M140 171h44" stroke="#8c3f22" stroke-width="3" />
                    <ellipse cx="162" cy="158" rx="25" ry="7" fill="#f3e3c3" stroke="#3a2340" stroke-width="3" />
                </g>
                <g class="cat__impact cat__impact--left"><path d="M60 140l-8-6M66 132l-4-9M128 140l8-6" /></g>
                <g class="cat__impact cat__impact--right"><path d="M132 140l-8-6M192 140l8-6M188 132l4-9" /></g>
            </g>

            <g class="cat__paws">
                <g class="cat__paw cat__paw--left">
                    <ellipse class="cat__fur" cx="95" cy="138" rx="17" ry="12" />
                    <path class="cat__toe" d="M88 146v-5M95 147v-5M102 146v-5" />
                </g>
                <g class="cat__paw cat__paw--right">
                    <ellipse class="cat__fur" cx="162" cy="140" rx="17" ry="12" />
                    <path class="cat__toe" d="M155 148v-5M162 149v-5M169 148v-5" />
                </g>
            </g>
        </g>
    </svg>
</template>

<style scoped>
.cat {
    --fur: #f4a261;
    --patch: transparent;
    --patch-alt: transparent;
    --stripe: transparent;
    --muzzle: transparent;
    --ear: #ff8fa3;
    --line: #3a2340;
    --eye: #2b1d33;
    --glint: #ffffff;
    display: block;
    width: 100%;
    height: auto;
}

.cat--coat-ginger { --fur: #f4a261; --stripe: #c9692e; --muzzle: #fbd8b3; }
.cat--coat-tuxedo { --fur: #2f2b45; --muzzle: #f4f0fa; --ear: #e7849a; --eye: #ffc857; --glint: #2f2b45; }
.cat--coat-smoke { --fur: #9aa0b4; --stripe: #7d8398; --muzzle: #c9cdd9; }
.cat--coat-calico { --fur: #f6efe4; --patch: #f2994a; --patch-alt: #3a3348; }
.cat--coat-midnight { --fur: #1f1b33; --ear: #6f5c8f; --eye: #ffc857; --glint: #1f1b33; --line: #0e0b1a; }
.cat--coat-cream { --fur: #f3e2c6; --muzzle: #fbf2e2; --ear: #f5a8b4; }

.cat__fur { fill: var(--fur); stroke: var(--line); stroke-width: 3; stroke-linejoin: round; }
.cat__inner-ear { fill: var(--ear); }
.cat__stripe { fill: none; stroke: var(--stripe); stroke-width: 4; stroke-linecap: round; }
.cat__patch--left { fill: var(--patch); }
.cat__patch--right { fill: var(--patch-alt); }
.cat__muzzle { fill: var(--muzzle); }
.cat__eyes--open circle { fill: var(--eye); }
.cat__eyes--open .cat__glint { fill: var(--glint); }
.cat__eyes--happy, .cat__eyes--asleep { fill: none; stroke: var(--line); stroke-width: 3; stroke-linecap: round; display: none; }
.cat__nose { fill: #ff8fa3; stroke: var(--line); stroke-width: 1.5; stroke-linejoin: round; }
.cat__mouth { fill: none; stroke: var(--line); stroke-width: 2.5; stroke-linecap: round; }
.cat__blush { fill: #ff8fa3; opacity: 0.45; }
.cat__whiskers { stroke: var(--line); stroke-width: 1.5; stroke-linecap: round; opacity: 0.55; }
.cat__toe { stroke: var(--line); stroke-width: 2; stroke-linecap: round; }
.cat__band { fill: none; stroke-width: 7; stroke-linecap: round; }
.cat__band--thick { stroke-width: 12; }

.cat--mood-purring .cat__eyes--open, .cat--mood-sleepy .cat__eyes--open { display: none; }
.cat--mood-purring .cat__eyes--happy { display: inline; }
.cat--mood-sleepy .cat__eyes--asleep { display: inline; }
.cat--slap .cat__eyes--open, .cat--slap .cat__eyes--asleep { display: none; }
.cat--slap .cat__eyes--happy { display: inline; }

.cat__zzz text { fill: var(--dusk); font-family: var(--font-display); font-size: 18px; }
.cat__zzz--small { font-size: 12px; }
.cat__purr path { fill: none; stroke: var(--lamp); stroke-width: 2.5; stroke-linecap: round; opacity: 0.7; }

.cat__paw { transform-box: fill-box; transform-origin: center; transform: translateY(-14px); transition: transform 300ms ease; }
.cat--mood-sleepy .cat__paw { transform: translateY(0); }
.cat__impact path { fill: none; stroke: var(--lamp); stroke-width: 3; stroke-linecap: round; opacity: 0; }

.cat--slap .cat__paw--left { animation: paw-slap 220ms ease-in 0ms 4 alternate; }
.cat--slap .cat__paw--right { animation: paw-slap 220ms ease-in 220ms 4 alternate; }
.cat--slap .cat__impact--left path { animation: impact 440ms ease-out 0ms 2; }
.cat--slap .cat__impact--right path { animation: impact 440ms ease-out 220ms 2; }
.cat--slap .cat__body { animation: bob 440ms ease-in-out 0ms 2; }

.cat__laser { animation: laser 2.4s ease-in-out infinite; }

@keyframes paw-slap {
    from { transform: translateY(-14px); }
    to { transform: translateY(6px) scaleY(0.92); }
}

@keyframes impact {
    0%, 40% { opacity: 0; }
    50% { opacity: 1; }
    100% { opacity: 0; }
}

@keyframes bob {
    50% { transform: translateY(2px); }
}

@keyframes laser {
    50% { transform: translateX(18px); }
}

@media (prefers-reduced-motion: reduce) {
    .cat--slap .cat__paw, .cat--slap .cat__impact path, .cat--slap .cat__body, .cat__laser { animation: none; }
}
</style>
