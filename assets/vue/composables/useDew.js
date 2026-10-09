import { useI18n } from 'vue-i18n';
import { duration } from '../greenhouse/duration.js';
import { ratePerHour } from '../greenhouse/tank.js';
import { intlLocale } from '../i18n/locale.js';

export function useDew() {
    const { t } = useI18n();
    const number = new Intl.NumberFormat(intlLocale());
    const rate = new Intl.NumberFormat(intlLocale(), { maximumFractionDigits: 1 });

    return {
        number: (value) => number.format(value),
        dew: (value) => t('greenhouse.amount', { n: number.format(value) }, value),
        rate: (rateMilliPerHour) => rate.format(ratePerHour(rateMilliPerHour)),
        duration: (seconds) => {
            const parts = duration(seconds);
            return t(`greenhouse.duration.${parts.unit}`, parts);
        },
    };
}
