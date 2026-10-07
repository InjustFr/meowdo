import SPECIES from './species.json';

const LICENSES = { cc0: 'CC0', 'cc-by': 'CC BY' };

export function speciesOf(slug) {
    const species = SPECIES[slug];
    return species ? { slug, ...species, licenseLabel: LICENSES[species.license] ?? species.license } : null;
}

export function allSpecies() {
    return Object.keys(SPECIES).map(speciesOf);
}
