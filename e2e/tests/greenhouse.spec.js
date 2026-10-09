import { expect, test } from '@playwright/test';
import { row, signInWithAccount, unique } from './support/session.js';

const railDew = (page) => page.locator('.shell__rail [data-test=dew] .tabular');

async function createPlantTask(page, title) {
    const created = await page.request.post('/api/tasks', { data: { title, plan: 'today', quadrant: 'schedule' } });
    expect(created.ok()).toBeTruthy();
    return (await created.json()).id;
}

test('completed tasks feed a greenhouse whose mosses raise every task reward', async ({ page }) => {
    const account = unique('greenhouse').replace(' ', '-');
    await page.goto('/login');
    await signInWithAccount(page, account, { email: `${account}@mossyleaf.test`, name: 'Gardener' });
    await page.waitForURL('/');
    await expect(railDew(page)).toHaveText('0');

    for (const title of ['Sow the basil', 'Prune the ferns', 'Repot the cactus', 'Mulch the beds', 'Water the moss', 'Rake the leaves'].map(unique)) {
        const id = await createPlantTask(page, title);
        expect((await page.request.post(`/api/tasks/${id}/complete`)).ok()).toBeTruthy();
    }
    await page.request.post('/api/achievements/seen');

    await page.goto('/greenhouse');
    await expect(page.getByRole('heading', { level: 1, name: 'Greenhouse' })).toBeVisible();
    await expect(page.getByTestId('dew-balance')).toHaveText('72');
    await expect(railDew(page)).toHaveText('72');
    await expect(page.getByTestId('yield')).toHaveText('0 / task');
    await expect(page.getByTestId('watering')).toHaveText('× 2');
    await expect(page.getByTestId('dew-source-schedule')).toContainText('12');
    await expect(page.getByTestId('dew-source-eliminate')).toContainText('1');
    await expect(page.getByTestId('expedition')).toBeDisabled();
    await expect(page.locator('.expedition')).toContainText('78 dew short');

    await page.getByTestId('pot-1').getByRole('button', { name: 'Plant a moss' }).click();
    const dialog = page.getByRole('dialog', { name: 'Plant a moss in pot 1' });
    await expect(dialog).toBeVisible();
    await dialog.locator('[data-test^=plant-]').first().click();
    await expect(dialog).toBeHidden();
    await expect(page.getByTestId('pot-1')).not.toContainText('Empty pot');
    await expect(page.getByTestId('pot-1').getByRole('button', { name: 'Change' })).toBeFocused();
    await expect(page.getByTestId('yield')).not.toHaveText('0 / task');
    await expect(page.getByTestId('dew-source-schedule')).toContainText('watering');

    await page.getByTestId('pot-2').getByRole('button', { name: 'Plant a moss' }).click();
    const second = page.getByRole('dialog', { name: 'Plant a moss in pot 2' });
    await expect(second.locator('[data-test^=plant-]').first()).toBeDisabled();
    await expect(second.getByRole('button', { name: 'Close' })).toBeFocused();
    await page.keyboard.press('Escape');
    await expect(page.getByTestId('pot-2').getByRole('button', { name: 'Plant a moss' })).toBeFocused();

    const title = unique('Plan the seedlings');
    await createPlantTask(page, title);
    await page.goto('/');
    await row(page, title).getByRole('checkbox', { name: `Complete “${title}”` }).click();
    await expect(page.locator('.shell__rail [data-test=dew-burst]').first()).toContainText(/\+\d+ dew · watered/);
    await expect.poll(async () => Number((await railDew(page).textContent()).replace(/\D/g, ''))).toBeGreaterThanOrEqual(88);

    await page.request.post('/api/achievements/seen');
    await page.goto('/greenhouse');
    const before = Number((await page.getByTestId('dew-balance').textContent()).replace(/\D/g, ''));
    await page.getByTestId('facility-glasshouse').getByRole('button', { name: 'Upgrade the Glasshouse to level 2' }).click();
    const confirm = page.getByRole('alertdialog', { name: 'Upgrade the Glasshouse to level 2' });
    await expect(confirm).toContainText('This spends 80 dew.');
    await confirm.getByRole('button', { name: 'Upgrade' }).click();
    await expect(page.getByTestId('dew-balance')).toHaveText(String(before - 80));
    await expect(page.getByTestId('facility-glasshouse')).toContainText('2 / 11');
    await expect(page.getByTestId('pot-3')).toContainText('Empty pot');

    await page.getByTestId('pot-1').getByRole('button', { name: /^Unplant / }).click();
    await expect(page.getByTestId('pot-1')).toContainText('Empty pot');
    await expect(page.getByTestId('pot-1').getByRole('button', { name: 'Plant a moss' })).toBeFocused();
    await expect(page.getByTestId('yield')).toHaveText('0 / task');
});
