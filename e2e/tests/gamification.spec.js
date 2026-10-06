import { expect, test } from '@playwright/test';
import { row, signIn, unique } from './support/session.js';

test('completing a task earns xp and coins once', async ({ page }) => {
    await signIn(page);
    const task = unique('Take out the bins');
    await page.getByLabel('New task').fill(task);
    await page.getByLabel('New task').press('Enter');

    const coins = page.locator('.shell__desk [data-test=coins]');
    const before = Number(await coins.textContent());

    await row(page, task).getByRole('checkbox', { name: `Complete “${task}”` }).click();
    await expect(page.locator('.shell__desk .cat-desk__burst').first()).toContainText('XP');
    await expect(coins).not.toHaveText(String(before));
    const after = Number(await coins.textContent());
    expect(after).toBeGreaterThan(before);

    await row(page, task).getByRole('checkbox', { name: `Reopen “${task}”` }).click();
    await row(page, task).getByRole('checkbox', { name: `Complete “${task}”` }).click();
    await page.waitForTimeout(500);
    await expect(coins).toHaveText(String(after));
});

test('buying an item puts it on the cat', async ({ page }) => {
    await signIn(page);
    await page.goto('/shop');
    const item = page.locator('.shop__item', { hasText: 'Ball of yarn' });
    if (await item.getByRole('button', { name: 'Buy' }).count()) {
        await item.getByRole('button', { name: 'Buy' }).click();
    } else if (await item.getByRole('button', { name: 'Wear' }).count()) {
        await item.getByRole('button', { name: 'Wear' }).click();
    }
    await expect(item.getByRole('button', { name: 'Take off' })).toBeVisible();
    await expect(item).toContainText('Wearing');
});
