<script setup>
import { computed } from 'vue';

const props = defineProps({
    tint: { type: String, default: 'sprout' },
    mood: { type: String, default: 'idle' },
    outfit: { type: Object, default: () => ({}) },
    cheering: { type: Boolean, default: false },
    label: { type: String, default: null },
});

const hat = computed(() => props.outfit.hat ?? null);
const neckwear = computed(() => props.outfit.neckwear ?? null);
const toy = computed(() => props.outfit.toy ?? null);
const backdrop = computed(() => props.outfit.backdrop ?? 'dawn');
const uid = `critter-${Math.random().toString(36).slice(2, 8)}`;
const SPOROPHYTES = [[18, 150, 26], [30, 152, 18], [226, 148, 24], [242, 152, 16]];
const RAIN = [[20, 10], [58, 34], [96, 4], [150, 28], [196, 8], [236, 30], [40, 74], [214, 70], [120, 60]];
const LEAVES = [[34, 46, 20, '#d9773a'], [74, 92, -30, '#c9552f'], [212, 104, 40, '#e0a03f'], [236, 58, -10, '#c9552f'], [186, 30, 60, '#d9773a']];
</script>

<template>
    <svg
        :class="['critter', `critter--tint-${tint}`, `critter--mood-${mood}`, { 'critter--cheer': cheering, 'critter--hatted': hat }]"
        viewBox="0 0 260 200"
        :role="label ? 'img' : undefined"
        :aria-label="label ?? undefined"
        :aria-hidden="label ? undefined : 'true'"
    >
        <defs>
            <clipPath :id="`${uid}-scene`"><rect x="0" y="0" width="260" height="200" rx="12" /></clipPath>
            <linearGradient :id="`${uid}-dawn`" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#f3f1e4" />
                <stop offset="1" stop-color="#dfe8d2" />
            </linearGradient>
            <linearGradient :id="`${uid}-rain`" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#cfd8dd" />
                <stop offset="1" stop-color="#e3e9e4" />
            </linearGradient>
            <linearGradient :id="`${uid}-autumn`" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#f6e7cf" />
                <stop offset="1" stop-color="#eed9bb" />
            </linearGradient>
        </defs>

        <g :clip-path="`url(#${uid}-scene)`">
            <g class="critter__backdrop">
                <template v-if="backdrop === 'pond'">
                    <rect width="260" height="200" fill="#e6efec" />
                    <ellipse cx="130" cy="140" rx="170" ry="40" fill="#b5d3d6" />
                    <path d="M-10 128q70-10 140 0t140 0" stroke="#d8ebec" stroke-width="3" fill="none" />
                    <path d="M40 132a18 7 0 1 0 30 0l-15 0z" fill="#7aa14a" />
                    <path d="M196 124a14 5 0 1 0 24 0l-12 0z" fill="#86ad55" />
                    <g stroke="#6b7f3a" stroke-width="3" stroke-linecap="round">
                        <path d="M226 130V64M236 132V78M18 132V82" />
                    </g>
                    <g fill="#8a5a32">
                        <rect x="222" y="54" width="8" height="22" rx="4" /><rect x="14" y="72" width="8" height="20" rx="4" />
                    </g>
                </template>
                <template v-else-if="backdrop === 'terrarium'">
                    <rect width="260" height="200" fill="#e7ede4" />
                    <path d="M28 200V60q0-44 102-44t102 44v140" fill="#f2f6ef" stroke="#c7d3c4" stroke-width="4" />
                    <rect x="98" y="4" width="64" height="16" rx="4" fill="#b88a5a" />
                    <path d="M50 70q4-28 30-38M60 120V82" stroke="#ffffff" stroke-width="5" fill="none" stroke-linecap="round" opacity="0.9" />
                    <path d="M200 60q14 24 4 60" stroke="#ffffff" stroke-width="3" fill="none" stroke-linecap="round" opacity="0.7" />
                    <path d="M196 150q-6-34 10-56M206 150q4-28 22-40M212 102q8-6 16-2" stroke="#5b7f3a" stroke-width="3" fill="none" stroke-linecap="round" />
                </template>
                <template v-else-if="backdrop === 'rainfall'">
                    <rect width="260" height="200" :fill="`url(#${uid}-rain)`" />
                    <g fill="#b9c5cc">
                        <circle cx="50" cy="26" r="22" /><circle cx="78" cy="20" r="26" /><circle cx="108" cy="30" r="18" />
                        <circle cx="186" cy="34" r="18" /><circle cx="212" cy="24" r="24" /><circle cx="238" cy="34" r="16" />
                    </g>
                    <g class="critter__rain" stroke="#8fa9b8" stroke-width="2" stroke-linecap="round">
                        <path v-for="([x, y], index) in RAIN" :key="index" :d="`M${x} ${y + 44}l-4 12`" />
                    </g>
                </template>
                <template v-else-if="backdrop === 'autumn'">
                    <rect width="260" height="200" :fill="`url(#${uid}-autumn)`" />
                    <path d="M-4 24q60 4 104 30M48 30q6-14 22-18M78 42q14-6 22 2" stroke="#7a5236" stroke-width="5" fill="none" stroke-linecap="round" />
                    <g class="critter__leaves">
                        <path
                            v-for="([x, y, angle, color], index) in LEAVES"
                            :key="index"
                            :d="`M${x} ${y}q8-10 16 0q-8 10-16 0z`"
                            :fill="color"
                            :transform="`rotate(${angle} ${x + 8} ${y})`"
                        />
                    </g>
                </template>
                <template v-else>
                    <rect width="260" height="200" :fill="`url(#${uid}-dawn)`" />
                    <circle cx="206" cy="46" r="22" fill="#fbf3d6" />
                    <g fill="none" stroke="#c4d4b2" stroke-width="3" stroke-linecap="round">
                        <path d="M26 150q-4-60 22-100M48 50q-14 8-22 4M44 66q12 0 18-8M38 86q-12 4-18-2M36 104q12 0 16-8" />
                        <path d="M234 150q6-44-10-80M224 70q10 4 18 0M230 90q-12 2-16-6M234 110q10-2 14-10" />
                    </g>
                </template>
            </g>

            <g class="critter__ground">
                <path d="M-10 200V160q14-14 30-6 10-12 28-6 14-10 30 0 16-8 32 0 16-10 32 0 16-8 30 0 14-10 30-2 18-10 30 0 12-6 18 0V200z" fill="#5f8a35" />
                <path d="M-10 168q16-10 34-2 14-8 30 0M200 162q16-8 30 0 14-6 30 2" stroke="#86b34f" stroke-width="4" fill="none" stroke-linecap="round" />
                <g v-for="([x, y, height], index) in SPOROPHYTES" :key="index">
                    <path :d="`M${x} ${y}q2-${height / 2} -1-${height}`" stroke="#8a6a3a" stroke-width="1.6" fill="none" />
                    <ellipse :cx="x - 1" :cy="y - height - 2" rx="2.4" ry="3.6" fill="#c98b4a" />
                </g>
            </g>

            <g class="critter__toy">
                <g v-if="toy === 'pebble'" transform="translate(216 160)">
                    <ellipse rx="16" ry="10" fill="#b8b4aa" stroke="var(--line)" stroke-width="2.5" />
                    <path d="M-8-4q6-4 12-2" stroke="#e4e1da" stroke-width="2.5" fill="none" stroke-linecap="round" />
                </g>
                <g v-else-if="toy === 'dewdrop'" transform="translate(220 160)">
                    <path d="M0-20q12 14 12 22a12 12 0 0 1-24 0q0-8 12-22z" fill="#bfe1ea" stroke="var(--line)" stroke-width="2.5" stroke-linejoin="round" />
                    <path d="M-5 0q0-6 4-10" stroke="#ffffff" stroke-width="2.5" fill="none" stroke-linecap="round" />
                </g>
                <g v-else-if="toy === 'snail'" transform="translate(214 162)">
                    <path d="M-18 6h34q6 0 8-8l2-6q-4 2-6 0" fill="#d9c7a1" stroke="var(--line)" stroke-width="2.5" stroke-linejoin="round" />
                    <circle cx="-2" cy="-4" r="12" fill="#c98b4a" stroke="var(--line)" stroke-width="2.5" />
                    <path d="M-2-4m-6 0a6 6 0 1 1 6 6a3 3 0 1 1-1-5" stroke="var(--line)" stroke-width="2" fill="none" stroke-linecap="round" />
                    <path d="M22-8l2-8M18-8l-1-8" stroke="var(--line)" stroke-width="2" stroke-linecap="round" />
                </g>
                <g v-else-if="toy === 'firefly'" transform="translate(212 104)">
                    <g class="critter__firefly">
                        <circle r="14" fill="#f6e27a" opacity="0.35" />
                        <ellipse cx="-2" rx="6" ry="4" fill="#3d3a2c" />
                        <circle cx="4" r="4" fill="#f6d84a" />
                        <path d="M-4-3q-2-8 4-8M0-3q4-7 8-4" stroke="#9bb3c2" stroke-width="2" fill="none" />
                    </g>
                </g>
            </g>

            <g class="critter__creature">
                <g class="critter__limbs">
                    <g class="critter__arm critter__arm--left">
                        <ellipse class="critter__skin" cx="70" cy="130" rx="13" ry="9" transform="rotate(-24 70 130)" />
                        <path class="critter__claw" d="M58 136l-4 2M58 131l-5-1" />
                    </g>
                    <g class="critter__arm critter__arm--right">
                        <ellipse class="critter__skin" cx="190" cy="130" rx="13" ry="9" transform="rotate(24 190 130)" />
                        <path class="critter__claw" d="M202 136l4 2M202 131l5-1" />
                    </g>
                    <g class="critter__foot">
                        <ellipse class="critter__skin" cx="106" cy="160" rx="14" ry="9" />
                        <path class="critter__claw" d="M98 167l-1 4M106 168v4M114 167l1 4" />
                    </g>
                    <g class="critter__foot">
                        <ellipse class="critter__skin" cx="154" cy="160" rx="14" ry="9" />
                        <path class="critter__claw" d="M146 167l-1 4M154 168v4M162 167l1 4" />
                    </g>
                </g>

                <g class="critter__body">
                    <path class="critter__skin" d="M70 126C68 90 94 64 130 64s62 26 60 62c-1 22-26 34-60 34s-59-12-60-34z" />
                    <ellipse class="critter__belly" cx="130" cy="136" rx="38" ry="18" />
                    <path class="critter__crease" d="M78 128q52 18 104 0M84 144q46 14 92 0" />
                    <g class="critter__tufts">
                        <path class="critter__stalk" d="M110 70q-3-12 1-22M150 68q4-10 1-20" />
                        <ellipse class="critter__capsule" cx="111" cy="45" rx="3" ry="4.5" transform="rotate(-10 111 45)" />
                        <ellipse class="critter__capsule" cx="151" cy="45" rx="3" ry="4.5" transform="rotate(12 151 45)" />
                        <path d="M80 94q-6-12 6-16 0-12 12-12 4-10 16-8 6-8 16-4 10-6 18 2 12-4 16 6 12-2 14 10 12 2 10 16-8-6-16-4-6-8-16-4-6-6-14-2-8-4-14 2-8-4-14 2-8-2-12 4-8-2-12 4-6 0-8 4z" />
                        <g class="critter__bumps">
                            <circle cx="98" cy="74" r="2" /><circle cx="118" cy="66" r="2" /><circle cx="140" cy="64" r="2" /><circle cx="160" cy="70" r="2" /><circle cx="128" cy="74" r="1.6" /><circle cx="108" cy="80" r="1.6" /><circle cx="150" cy="78" r="1.6" />
                        </g>
                    </g>
                    <g class="critter__face">
                        <g class="critter__eyes critter__eyes--open">
                            <circle cx="112" cy="100" r="5" /><circle cx="148" cy="100" r="5" />
                            <circle class="critter__glint" cx="114" cy="98" r="1.6" /><circle class="critter__glint" cx="150" cy="98" r="1.6" />
                        </g>
                        <path class="critter__eyes critter__eyes--happy" d="M105 102q7-9 14 0M141 102q7-9 14 0" />
                        <path class="critter__eyes critter__eyes--closed" d="M105 100q7 5 14 0M141 100q7 5 14 0" />
                        <circle class="critter__mouth" cx="130" cy="114" r="4.5" />
                        <ellipse class="critter__blush" cx="100" cy="112" rx="7" ry="4" /><ellipse class="critter__blush" cx="160" cy="112" rx="7" ry="4" />
                    </g>
                </g>

                <g class="critter__neckwear">
                    <g v-if="neckwear === 'leaf-collar'">
                        <path d="M82 126q48 16 96 0" stroke="#7a5a32" stroke-width="2.5" fill="none" />
                        <g fill="#86b34f" stroke="var(--line)" stroke-width="2" stroke-linejoin="round">
                            <path d="M92 128q4-10 12-8-2 10-12 8z" /><path d="M112 134q4-10 12-8-2 10-12 8z" />
                            <path d="M136 134q8-2 12 8-10 2-12-8z" /><path d="M156 128q8-2 12 8-10 2-12-8z" />
                        </g>
                    </g>
                    <g v-else-if="neckwear === 'bow-tie'">
                        <path d="M130 130l-18-10v20zM130 130l18-10v20z" fill="#d9773a" stroke="var(--line)" stroke-width="2.5" stroke-linejoin="round" />
                        <circle cx="130" cy="130" r="5" fill="#b85a26" stroke="var(--line)" stroke-width="2.5" />
                    </g>
                    <g v-else-if="neckwear === 'bandana'">
                        <path d="M82 124q48 16 96 0l-48 30z" fill="#c9552f" stroke="var(--line)" stroke-width="2.5" stroke-linejoin="round" />
                        <g fill="#fbeee6"><circle cx="112" cy="132" r="2" /><circle cx="130" cy="142" r="2" /><circle cx="148" cy="132" r="2" /></g>
                    </g>
                    <g v-else-if="neckwear === 'scarf'">
                        <path d="M78 122q52 20 104 0" stroke="#e0a03f" stroke-width="12" fill="none" stroke-linecap="round" />
                        <path d="M150 130l8 26h-14z" fill="#e0a03f" stroke="var(--line)" stroke-width="2.5" stroke-linejoin="round" />
                        <path d="M94 126l-4 6M110 130l-3 7M126 132v7M142 132l2 7M166 128l4 6" stroke="#b97a22" stroke-width="2.5" />
                    </g>
                </g>

                <g class="critter__hat" transform="translate(130 76) scale(1.15) translate(-130 -76)">
                    <g v-if="hat === 'acorn-cap'">
                        <path d="M98 72q32-34 64 0z" fill="#a8743f" stroke="var(--line)" stroke-width="3" stroke-linejoin="round" />
                        <path d="M106 64l8-8M118 60l8-10M132 58l8-8M144 62l8-6" stroke="#7d5229" stroke-width="2.5" stroke-linecap="round" />
                        <path d="M130 56q2-8 8-10" stroke="var(--line)" stroke-width="3.5" fill="none" stroke-linecap="round" />
                    </g>
                    <g v-else-if="hat === 'beanie'">
                        <path d="M100 72q30-48 60 0z" fill="#6b8fb0" stroke="var(--line)" stroke-width="3" stroke-linejoin="round" />
                        <rect x="98" y="64" width="64" height="10" rx="4" fill="#557a99" stroke="var(--line)" stroke-width="3" />
                        <circle cx="130" cy="40" r="7" fill="#f1efe6" stroke="var(--line)" stroke-width="2.5" />
                    </g>
                    <g v-else-if="hat === 'toadstool'">
                        <path d="M122 74v-10h16v10z" fill="#f4ecd8" stroke="var(--line)" stroke-width="3" stroke-linejoin="round" />
                        <path d="M90 66q40-56 80 0q-40 8-80 0z" fill="#c9412f" stroke="var(--line)" stroke-width="3" stroke-linejoin="round" />
                        <g fill="#fbf3e4"><circle cx="112" cy="50" r="5" /><circle cx="134" cy="40" r="6" /><circle cx="152" cy="54" r="4" /><circle cx="124" cy="60" r="3" /></g>
                    </g>
                    <g v-else-if="hat === 'wizard-hat'">
                        <ellipse cx="130" cy="68" rx="40" ry="8" fill="#4f5d8a" stroke="var(--line)" stroke-width="3" />
                        <path d="M108 68q10-34 34-56 2 30 10 56z" fill="#5f6fa3" stroke="var(--line)" stroke-width="3" stroke-linejoin="round" />
                        <path d="M130 40l2 5 5 1-4 3 1 5-4-3-4 3 1-5-4-3 5-1z" fill="#f2d16b" />
                        <circle cx="124" cy="58" r="2" fill="#f2d16b" /><circle cx="140" cy="52" r="1.6" fill="#f2d16b" />
                    </g>
                    <g v-else-if="hat === 'flower-crown'">
                        <path d="M96 72q34-14 68 0" stroke="#5b7f3a" stroke-width="4" fill="none" stroke-linecap="round" />
                        <g v-for="([x, y, color], index) in [[100, 68, '#f2b8c6'], [116, 62, '#f6e27a'], [132, 60, '#ffffff'], [148, 62, '#f2b8c6'], [162, 68, '#f6e27a']]" :key="index" :transform="`translate(${x} ${y})`">
                            <circle cx="0" cy="-5" r="4" :fill="color" stroke="var(--line)" stroke-width="1.5" />
                            <circle cx="5" cy="0" r="4" :fill="color" stroke="var(--line)" stroke-width="1.5" />
                            <circle cx="0" cy="5" r="4" :fill="color" stroke="var(--line)" stroke-width="1.5" />
                            <circle cx="-5" cy="0" r="4" :fill="color" stroke="var(--line)" stroke-width="1.5" />
                            <circle r="2.6" fill="#e0a03f" />
                        </g>
                    </g>
                </g>
            </g>

            <g class="critter__mood-mark">
                <g v-if="mood === 'dormant'" class="critter__zzz">
                    <text x="182" y="70">z</text><text x="194" y="56" class="critter__zzz--small">z</text>
                </g>
                <g v-else-if="mood === 'lively'" class="critter__sparkles">
                    <path d="M200 76l2 6 6 2-6 2-2 6-2-6-6-2 6-2zM58 70l1.5 4 4 1.5-4 1.5-1.5 4-1.5-4-4-1.5 4-1.5z" />
                </g>
            </g>

            <g class="critter__moss-lip">
                <path d="M-10 200v-22q12-8 24-2 12-8 24 0 10-6 20 2l10 22zM270 200v-22q-12-8-24-2-12-8-24 0-10-6-20 2l-10 22z" fill="#4d7a2c" />
            </g>

            <g class="critter__dew">
                <path class="critter__drop" d="M130 34q9 11 9 17a9 9 0 0 1-18 0q0-6 9-17z" />
                <g class="critter__splash"><path d="M112 58l-8-6M114 50l-4-8M148 58l8-6M146 50l4-8" /></g>
            </g>
        </g>
    </svg>
</template>

<style scoped>
.critter {
    --skin: #9cc56b;
    --belly: #d9eabd;
    --tuft: #5b7f3a;
    --line: #2b3323;
    --eye: #1f251a;
    display: block;
    width: 100%;
    height: auto;
}

.critter--tint-sprout { --skin: #a7cc76; --belly: #e1efc8; --tuft: #4f7a2c; }
.critter--tint-lichen { --skin: #b5c2a4; --belly: #e4e9da; --tuft: #6f9a3e; }
.critter--tint-peat { --skin: #b08868; --belly: #e3cfb9; --tuft: #6f9a3e; }
.critter--tint-rust { --skin: #e09a62; --belly: #f4d7b8; --tuft: #5f8a35; }
.critter--tint-frost { --skin: #b4cbd6; --belly: #e6eff3; --tuft: #6f9a3e; }
.critter--tint-plum { --skin: #b39ab6; --belly: #e4d8e6; --tuft: #6f9a3e; }

.critter__skin { fill: var(--skin); stroke: var(--line); stroke-width: 3; stroke-linejoin: round; }
.critter__belly { fill: var(--belly); }
.critter__crease { fill: none; stroke: var(--line); stroke-width: 2; stroke-linecap: round; opacity: 0.22; }
.critter__tufts path { fill: var(--tuft); stroke: var(--line); stroke-width: 2.5; stroke-linejoin: round; }
.critter__tufts .critter__stalk { fill: none; stroke: #8a6a3a; stroke-width: 2; stroke-linecap: round; }
.critter__tufts .critter__capsule { fill: #c98b4a; stroke: var(--line); stroke-width: 1.5; }
.critter--hatted .critter__stalk, .critter--hatted .critter__capsule { display: none; }
.critter__bumps circle { fill: color-mix(in oklch, var(--tuft) 60%, #ffffff); stroke: none; }
.critter__claw { fill: none; stroke: var(--line); stroke-width: 2; stroke-linecap: round; }
.critter__eyes--open circle { fill: var(--eye); }
.critter__eyes--open .critter__glint { fill: #ffffff; }
.critter__eyes--happy, .critter__eyes--closed { display: none; fill: none; stroke: var(--line); stroke-width: 3; stroke-linecap: round; }
.critter__mouth { fill: #6b3a3a; stroke: var(--line); stroke-width: 2.5; }
.critter__blush { fill: #e88c8c; opacity: 0.4; }

.critter--mood-lively .critter__eyes--open, .critter--mood-dormant .critter__eyes--open { display: none; }
.critter--mood-lively .critter__eyes--happy { display: inline; }
.critter--mood-dormant .critter__eyes--closed { display: inline; }
.critter--cheer .critter__eyes--open, .critter--cheer .critter__eyes--closed { display: none; }
.critter--cheer .critter__eyes--happy { display: inline; }

.critter__zzz text { fill: #7d8771; font-family: var(--font-display); font-size: 18px; }
.critter__zzz--small { font-size: 12px; }
.critter__sparkles path { fill: #e9b949; }

.critter__body, .critter__limbs, .critter__neckwear, .critter__hat, .critter__arm, .critter__foot { transform-box: fill-box; transition: transform 400ms ease; }
.critter__body, .critter__creature { transform-origin: 50% 100%; }
.critter__creature { transform-box: fill-box; transition: transform 400ms ease, filter 400ms ease; }
.critter__arm--left { transform-origin: 100% 30%; }
.critter__arm--right { transform-origin: 0% 30%; }
.critter__foot { transform-origin: 50% 0%; }

.critter--mood-dormant .critter__creature { transform: scale(0.94, 0.88); filter: saturate(0.55); }
.critter--mood-dormant .critter__arm--left { transform: translate(10px, -4px) scale(0.5); }
.critter--mood-dormant .critter__arm--right { transform: translate(-10px, -4px) scale(0.5); }
.critter--mood-dormant .critter__foot { transform: translateY(-8px) scale(0.6); }

.critter__drop { fill: #bfe1ea; stroke: var(--line); stroke-width: 2.5; stroke-linejoin: round; opacity: 0; transform: translateY(-60px); }
.critter__splash path { fill: none; stroke: #8fc3d1; stroke-width: 3; stroke-linecap: round; opacity: 0; }

.critter--cheer .critter__drop { animation: drop-fall 420ms cubic-bezier(0.5, 0, 0.9, 0.5) forwards; }
.critter--cheer .critter__splash path { animation: splash 520ms ease-out 400ms forwards; }
.critter--cheer .critter__creature { animation: squish 600ms cubic-bezier(0.3, 1.4, 0.5, 1) 400ms; }
.critter--cheer .critter__arm--left { animation: wave-left 260ms ease-in-out 460ms 4 alternate; }
.critter--cheer .critter__arm--right { animation: wave-right 260ms ease-in-out 460ms 4 alternate; }

.critter__rain path { animation: rain 900ms linear infinite; }
.critter__rain path:nth-child(3n + 1) { animation-delay: -300ms; }
.critter__rain path:nth-child(3n + 2) { animation-delay: -600ms; }
.critter__leaves path { animation: leaf 5s ease-in-out infinite alternate; transform-box: fill-box; transform-origin: center; }
.critter__leaves path:nth-child(2n) { animation-duration: 6.5s; }
.critter__firefly { animation: hover 3s ease-in-out infinite; }

@keyframes drop-fall {
    0% { opacity: 1; transform: translateY(-60px); }
    90% { opacity: 1; transform: translateY(10px) scale(1); }
    100% { opacity: 0; transform: translateY(14px) scale(1.4, 0.4); }
}

@keyframes splash {
    0% { opacity: 1; }
    100% { opacity: 0; transform: translateY(-4px); }
}

@keyframes squish {
    0% { transform: scale(1); }
    25% { transform: scale(1.06, 0.9); }
    60% { transform: scale(0.97, 1.05); }
    100% { transform: scale(1); }
}

@keyframes wave-left { to { transform: rotate(22deg); } }
@keyframes wave-right { to { transform: rotate(-22deg); } }
@keyframes rain { from { transform: translate(0, -40px); } to { transform: translate(-10px, 60px); } }
@keyframes leaf { to { transform: translate(6px, 10px) rotate(30deg); } }
@keyframes hover { 50% { transform: translate(-10px, 6px); } }

@media (prefers-reduced-motion: reduce) {
    .critter--cheer .critter__drop, .critter--cheer .critter__splash path, .critter--cheer .critter__creature,
    .critter--cheer .critter__arm, .critter__rain path, .critter__leaves path, .critter__firefly { animation: none; }
}
</style>
