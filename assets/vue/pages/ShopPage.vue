<script setup>
import { computed, ref, watch } from 'vue';
import { Check, Coins, Lock } from '@lucide/vue';
import { RadioGroupItem, RadioGroupRoot } from 'reka-ui';
import { useI18n } from 'vue-i18n';
import BaseButton from '../components/ui/BaseButton.vue';
import CritterSvg from '../components/critter/CritterSvg.vue';
import FormField from '../components/ui/FormField.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import PageSection from '../components/ui/PageSection.vue';
import { useApi } from '../composables/useApi.js';
import { usePlayer } from '../composables/usePlayer.js';
import { useToast } from '../composables/useToast.js';

const TINTS = ['sprout', 'lichen', 'peat', 'rust', 'frost', 'plum'];
const SLOTS = ['hat', 'neckwear', 'toy', 'backdrop'];

const { t } = useI18n();
const toast = useToast();
const { player, buy, wear, takeOff, rename, retint } = usePlayer();
const items = ref(null);
useApi().load('/api/shop', items);

const preview = ref(null);
const name = ref('');
watch(() => player.value?.critter.name, (value) => { name.value = value ?? ''; }, { immediate: true });

const outfit = computed(() => {
    const worn = { ...player.value.critter.outfit };
    if (preview.value) worn[preview.value.slot] = preview.value.slug;
    return worn;
});

const bySlot = computed(() => Object.fromEntries(SLOTS.map((slot) => [slot, (items.value ?? []).filter((item) => item.slot === slot)])));

function locked(item) {
    return !item.owned && player.value.level < item.minLevel;
}

function affordable(item) {
    return player.value.coins >= item.price;
}

async function run(action, success) {
    try {
        await action();
        if (success) toast.success(success);
    } catch (error) {
        toast.error(error.message);
    }
}

function act(item) {
    if (item.worn) return run(() => takeOff(item.slot));
    if (item.owned) return run(() => wear(item.slug));
    return run(() => buy(item.slug), t('shop.bought', { item: t(`shop.items.${item.slug}`), name: player.value.critter.name }));
}

function saveName() {
    if (name.value.trim() && name.value.trim() !== player.value.critter.name) run(() => rename(name.value.trim()), t('shop.renamed', { name: name.value.trim() }));
}
</script>

<template>
    <div v-if="player" class="page page--wide">
        <PageHeader :title="t('shop.title', { name: player.critter.name })" :subtitle="t('shop.subtitle')">
            <span class="shop__purse tabular"><Coins size="1.125rem" aria-hidden="true" />{{ player.coins }}</span>
        </PageHeader>
        <div class="shop">
            <aside class="shop__studio">
                <div class="shop__preview">
                    <CritterSvg :tint="player.critter.tint" mood="lively" :outfit="outfit" :label="t('shop.previewLabel', { name: player.critter.name })" />
                </div>
                <form class="shop__name" @submit.prevent="saveName">
                    <FormField :label="t('shop.name')">
                        <input v-model="name" type="text" maxlength="30" @blur="saveName">
                    </FormField>
                </form>
                <FormField :label="t('shop.tint')" as="div">
                    <RadioGroupRoot :model-value="player.critter.tint" class="shop__tints" orientation="horizontal" @update:model-value="(tint) => run(() => retint(tint))">
                        <RadioGroupItem v-for="tint in TINTS" :key="tint" :value="tint" :class="['shop__tint', `shop__tint--${tint}`]" :aria-label="t(`shop.tints.${tint}`)">
                            <Check v-if="player.critter.tint === tint" size="1rem" :stroke-width="3" aria-hidden="true" />
                        </RadioGroupItem>
                    </RadioGroupRoot>
                </FormField>
            </aside>
            <div class="shop__catalog">
                <PageSection v-for="slot in SLOTS" :key="slot" :title="t(`shop.slots.${slot}`)">
                    <ul class="shop__items">
                        <li
                            v-for="item in bySlot[slot]"
                            :key="item.slug"
                            :class="['shop__item', { 'shop__item--worn': item.worn, 'shop__item--locked': locked(item) }]"
                            @mouseenter="preview = item"
                            @mouseleave="preview = null"
                            @focusin="preview = item"
                            @focusout="preview = null"
                        >
                            <span class="shop__item-name">{{ t(`shop.items.${item.slug}`) }}</span>
                            <span v-if="locked(item)" class="shop__item-meta"><Lock size="0.875rem" aria-hidden="true" />{{ t('shop.unlocksAt', { level: item.minLevel }) }}</span>
                            <span v-else-if="!item.owned" :class="['shop__item-meta', 'shop__price', { 'shop__price--short': !affordable(item) }]"><Coins size="0.875rem" aria-hidden="true" />{{ item.price }}</span>
                            <span v-else class="shop__item-meta">{{ item.worn ? t('shop.wearing') : t('shop.owned') }}</span>
                            <BaseButton
                                :variant="item.owned ? 'secondary' : 'primary'"
                                :disabled="locked(item) || (!item.owned && !affordable(item))"
                                @click="act(item)"
                            >
                                {{ item.worn ? t('shop.takeOff') : item.owned ? t('shop.wear') : t('shop.buy') }}
                            </BaseButton>
                        </li>
                    </ul>
                </PageSection>
            </div>
        </div>
    </div>
</template>

<style scoped>
.shop { display: grid; grid-template-columns: minmax(16rem, 22rem) minmax(0, 1fr); align-items: start; gap: var(--space-6); }
.shop__studio { position: sticky; top: var(--space-5); display: flex; flex-direction: column; gap: var(--space-4); }
.shop__preview { overflow: hidden; border: 0.0625rem solid var(--color-border); border-radius: var(--radius); }
.shop__purse { display: inline-flex; align-items: center; gap: var(--space-2); padding: var(--space-1) var(--space-3); border: 0.0625rem solid var(--color-border-strong); border-radius: var(--radius-pill); background: var(--color-surface); color: var(--color-ink); font-weight: 600; }
.shop__purse svg { color: var(--color-warning); }
.shop__tints { display: flex; flex-wrap: wrap; gap: var(--space-2); }
.shop__tint { display: grid; place-items: center; width: 2.25rem; height: 2.25rem; border: 0.0625rem solid var(--color-border-strong); border-radius: 50%; color: #2b3323; cursor: pointer; transition: box-shadow var(--transition); }
.shop__tint:hover { box-shadow: 0 0 0 0.1875rem var(--color-border); }
.shop__tint[data-state="checked"] { box-shadow: 0 0 0 0.125rem var(--color-surface), 0 0 0 0.25rem var(--color-accent); }
.shop__tint--sprout { background: #a7cc76; }
.shop__tint--lichen { background: #b5c2a4; }
.shop__tint--peat { background: #b08868; }
.shop__tint--rust { background: #e09a62; }
.shop__tint--frost { background: #b4cbd6; }
.shop__tint--plum { background: #b39ab6; }
.shop__catalog { display: flex; flex-direction: column; gap: var(--space-6); }
.shop__items { display: grid; grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr)); gap: var(--space-3); margin: 0; padding: 0; list-style: none; }
.shop__item { display: flex; flex-direction: column; align-items: flex-start; gap: var(--space-2); padding: var(--space-4); border: 0.0625rem solid var(--color-border); border-radius: var(--radius); background: var(--color-surface); transition: border-color var(--transition); }
.shop__item:hover { border-color: var(--color-border-strong); }
.shop__item--worn { border-color: var(--color-accent); background: var(--color-accent-soft); }
.shop__item--locked { border-style: dashed; background: transparent; }
.shop__item-name { color: var(--color-ink); font-weight: 600; }
.shop__item-meta { display: inline-flex; align-items: center; gap: var(--space-1); color: var(--color-muted); font-size: var(--font-size-sm); }
.shop__price { color: var(--color-ink); font-weight: 600; }
.shop__price svg { color: var(--color-warning); }
.shop__price--short { color: var(--color-subtle); }
.shop__item :deep(.button) { margin-top: auto; }

@media (max-width: 56rem) {
    .shop { grid-template-columns: 1fr; }
    .shop__studio { position: static; }
}
</style>
