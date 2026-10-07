import { expect, test } from '@playwright/test';
import { row, signIn, unique } from './support/session.js';

test('statistics count a newly completed task', async ({ page }) => {
    await signIn(page);
    await page.goto('/stats');
    const completed = page.locator('.stat-tile', { hasText: 'Completed' }).locator('.stat-tile__value');
    await expect(completed).toBeVisible();
    const before = Number((await completed.textContent()).replace(/\D/g, ''));

    await page.locator('.shell__rail').getByRole('link', { name: 'Today' }).click();
    const task = unique('Sweep the porch');
    await page.getByLabel('New task').fill(task);
    await page.getByLabel('New task').press('Enter');
    const completion = page.waitForResponse((response) => response.url().endsWith('/complete') && response.ok());
    await row(page, task).getByRole('checkbox', { name: `Complete “${task}”` }).click();
    await completion;
    await expect(row(page, task).getByRole('checkbox', { name: `Reopen “${task}”` })).toBeVisible();

    await page.locator('.shell__rail').getByRole('link', { name: 'Statistics', exact: true }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Statistics' })).toBeVisible();
    await expect(completed).toHaveText(new Intl.NumberFormat('en').format(before + 1));
    await expect(page.getByRole('img', { name: /completed over the last 30 days/ })).toBeVisible();
});
