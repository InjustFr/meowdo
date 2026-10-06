<script setup>
import { computed, ref, watch } from 'vue';
import { Check, Coins, Lock } from '@lucide/vue';
import { RadioGroupItem, RadioGroupRoot } from 'reka-ui';
import { useI18n } from 'vue-i18n';
import BaseButton from '../components/ui/BaseButton.vue';
import CatSvg from '../components/cat/CatSvg.vue';
import FormField from '../components/ui/FormField.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import PageSection from '../components/ui/PageSection.vue';
import { useApi } from '../composables/useApi.js';
import { usePlayer } from '../composables/usePlayer.js';
import { useToast } from '../composables/useToast.js';

const COATS = ['ginger', 'tuxedo', 'smoke', 'calico', 'midnight', 'cream'];
const SLOTS = ['hat', 'neckwear', 'toy', 'backdrop'];

const { t } = useI18n();
const toast = useToast();
const { player, buy, wear, takeOff, rename, recoat } = usePlayer();
const items = ref(null);
useApi().load('/api/shop', items);

const preview = ref(null);
const name = ref('');
watch(() => player.value?.cat.name, (value) => { name.value = value ?? ''; }, { immediate: true });

const outfit = computed(() => {
    const worn = { ...player.value.cat.outfit };
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
    return run(() => buy(item.slug), t('shop.bought', { item: t(`shop.items.${item.slug}`), name: player.value.cat.name }));
}

function saveName() {
    if (name.value.trim() && name.value.trim() !== player.value.cat.name) run(() => rename(name.value.trim()), t('shop.renamed', { name: name.value.trim() }));
}
</script>

<template>
    <div v-if="player" class="page page--wide">
        <PageHeader :title="t('shop.title', { name: player.cat.name })" :subtitle="t('shop.subtitle')">
            <span class="shop__purse tabular"><Coins size="1.125rem" aria-hidden="true" />{{ player.coins }}</span>
        </PageHeader>
        <div class="shop">
            <aside class="shop__studio">
                <div class="shop__preview">
                    <CatSvg :coat="player.cat.coat" mood="purring" :outfit="outfit" :label="t('shop.previewLabel', { name: player.cat.name })" />
                </div>
                <form class="shop__name" @submit.prevent="saveName">
                    <FormField :label="t('shop.name')">
                        <input v-model="name" type="text" maxlength="30" @blur="saveName">
                    </FormField>
                </form>
                <FormField :label="t('shop.coat')" as="div">
                    <RadioGroupRoot :model-value="player.cat.coat" class="shop__coats" orientation="horizontal" @update:model-value="(coat) => run(() => recoat(coat))">
                        <RadioGroupItem v-for="coat in COATS" :key="coat" :value="coat" :class="['shop__coat', `shop__coat--${coat}`]" :aria-label="t(`shop.coats.${coat}`)">
                            <Check v-if="player.cat.coat === coat" size="1rem" :stroke-width="3" aria-hidden="true" />
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
.shop__preview { overflow: hidden; border-radius: var(--radius-panel); box-shadow: var(--shadow); }
.shop__purse { display: inline-flex; align-items: center; gap: var(--space-2); padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill); background: var(--color-accent-soft); color: var(--lamp); font-family: var(--font-display); font-size: 1.25rem; }
.shop__coats { display: flex; flex-wrap: wrap; gap: var(--space-2); }
.shop__coat { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border: 0.1875rem solid transparent; border-radius: 50%; color: var(--night); cursor: pointer; }
.shop__coat[data-state="checked"] { border-color: var(--moonmilk); }
.shop__coat--ginger { background: #f4a261; }
.shop__coat--tuxedo { background: linear-gradient(135deg, #2f2b45 55%, #f4f0fa 55%); color: var(--lamp); }
.shop__coat--smoke { background: #9aa0b4; }
.shop__coat--calico { background: conic-gradient(#f2994a 0 30%, #f6efe4 0 70%, #3a3348 0); }
.shop__coat--midnight { background: #1f1b33; color: var(--lamp); box-shadow: inset 0 0 0 0.0625rem var(--color-line-strong); }
.shop__coat--cream { background: #f3e2c6; }
.shop__catalog { display: flex; flex-direction: column; gap: var(--space-6); }
.shop__items { display: grid; grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr)); gap: var(--space-3); margin: 0; padding: 0; list-style: none; }
.shop__item { display: flex; flex-direction: column; align-items: flex-start; gap: var(--space-2); padding: var(--space-4); border-radius: var(--radius-row); background: var(--color-surface); }
.shop__item--worn { box-shadow: inset 0 0 0 0.125rem var(--lamp); }
.shop__item--locked { opacity: 0.6; }
.shop__item-name { font-weight: 700; }
.shop__item-meta { display: inline-flex; align-items: center; gap: var(--space-1); color: var(--color-muted); font-size: var(--font-size-sm); }
.shop__price { color: var(--lamp); font-weight: 700; }
.shop__price--short { color: var(--color-subtle); }
.shop__item :deep(.button) { margin-top: auto; }

@media (max-width: 56rem) {
    .shop { grid-template-columns: 1fr; }
    .shop__studio { position: static; }
}
</style>
