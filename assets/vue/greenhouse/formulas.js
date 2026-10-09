const TENTHS = 10;
const PERCENT = 100;
const HALF = 2;

export function yieldBreakdown(pots, mistersPercent) {
    const terms = pots.filter((pot) => pot.species).map((pot) => pot.yield);

    return {
        terms,
        sum: terms.reduce((total, term) => total + term, 0),
        factor: (PERCENT + mistersPercent) / PERCENT,
    };
}

export function bonusBreakdown(quadrant, yieldTenths, wateringMultiplier) {
    const shares = {
        schedule: { multiplier: wateringMultiplier, divisor: TENTHS },
        do_first: { multiplier: wateringMultiplier, divisor: HALF * TENTHS },
        delegate: { multiplier: 1, divisor: TENTHS },
    };
    const share = shares[quadrant];
    if (!share) return null;

    const numerator = yieldTenths * share.multiplier;

    return {
        yield: yieldTenths / TENTHS,
        multiplier: share.multiplier,
        halved: share.divisor === HALF * TENTHS,
        exact: numerator / share.divisor,
        rounded: numerator % share.divisor !== 0,
    };
}
