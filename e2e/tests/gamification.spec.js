import { expect, test } from '@playwright/test';
import { row, signIn, signInWithAccount, unique } from './support/session.js';

test('completing a task earns xp once', async ({ page }) => {
    await signIn(page);
    const task = unique('Take out the bins');
    await page.getByLabel('New task').fill(task);
    await page.getByLabel('New task').press('Enter');

    const bursts = page.locator('.shell__rail .player-progress__burst');
    await row(page, task).getByRole('checkbox', { name: `Complete “${task}”` }).click();
    await expect(bursts.first()).toContainText('XP');
    await expect(bursts).toHaveCount(0);

    await row(page, task).getByRole('checkbox', { name: `Reopen “${task}”` }).click();
    await row(page, task).getByRole('checkbox', { name: `Complete “${task}”` }).click();
    await page.waitForTimeout(500);
    await expect(bursts).toHaveCount(0);
});

test('crossing a level reveals a new moss for the herbarium', async ({ page }) => {
    const account = unique('moss').replace(' ', '-');
    await page.goto('/login');
    await signInWithAccount(page, account, { email: `${account}@mossyleaf.test`, name: 'Moss' });
    await page.waitForURL('/');
    await expect(page.locator('.shell__rail [data-test=species]')).toHaveText('0 of 48 mosses');

    const titles = ['Prune the ferns', 'Repot the cactus', 'Sow the basil', 'Mulch the beds', 'Water the moss', 'Rake the leaves'].map(unique);
    const ids = [];
    for (const title of titles) {
        const created = await page.request.post('/api/tasks', { data: { title, plan: 'today', quadrant: 'schedule' } });
        expect(created.ok()).toBeTruthy();
        ids.push((await created.json()).id);
    }
    for (const id of ids.slice(0, 5)) {
        expect((await page.request.post(`/api/tasks/${id}/complete`)).ok()).toBeTruthy();
    }
    await page.request.post('/api/achievements/seen');
    await page.reload();

    await row(page, titles[5]).getByRole('checkbox', { name: `Complete “${titles[5]}”` }).click();
    const celebration = page.getByRole('dialog', { name: 'Level 2' });
    await expect(celebration).toBeVisible();
    await expect(celebration.locator('[data-test=species-card]')).toHaveCount(1);
    await expect(celebration).toContainText('moss joins your herbarium');
    await celebration.getByRole('button', { name: 'Continue' }).click();

    await expect(page.locator('.shell__rail [data-test=species]')).toHaveText('1 of 48 mosses');
    await page.goto('/herbarium');
    await expect(page.getByRole('heading', { level: 1, name: 'Herbarium' })).toBeVisible();
    await expect(page.locator('.shell__main [data-test=species-card]')).toHaveCount(1);
    await expect(page.locator('.herbarium__slot')).toHaveCount(47);
});
