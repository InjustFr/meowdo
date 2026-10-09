import { useI18n } from 'vue-i18n';
import { intlLocale } from '../i18n/locale.js';

const TENTHS = 10;

export function useDew() {
    const { t } = useI18n();
    const number = new Intl.NumberFormat(intlLocale());
    const tenths = new Intl.NumberFormat(intlLocale(), { maximumFractionDigits: 1 });
    const hundredths = new Intl.NumberFormat(intlLocale(), { maximumFractionDigits: 2 });

    return {
        number: (value) => number.format(value),
        dew: (value) => t('greenhouse.amount', { n: number.format(value) }, value),
        decimal: (value) => tenths.format(value),
        precise: (value) => hundredths.format(value),
        yieldOf: (yieldTenths) => tenths.format(yieldTenths / TENTHS),
    };
}
