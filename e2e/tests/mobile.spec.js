import { expect, test } from '@playwright/test';
import { signIn } from './support/session.js';

test('the tab bar navigates on a phone', async ({ page }) => {
    await signIn(page);
    await expect(page.locator('.shell__strip .player-progress')).toBeVisible();
    await page.locator('.shell__tabs').getByRole('link', { name: 'Matrix' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Matrix' })).toBeVisible();
    await page.locator('.shell__tabs').getByRole('link', { name: 'Inbox' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Inbox' })).toBeVisible();
    await page.locator('.shell__tabs').getByRole('button', { name: 'Menu' }).click();
    const menu = page.getByRole('dialog', { name: 'Menu' });
    await menu.getByRole('link', { name: 'Projects' }).click();
    await expect(menu).toBeHidden();
    await expect(page.getByRole('heading', { level: 1, name: 'Projects' })).toBeVisible();
});

test('the greenhouse opens from the menu and fits a phone', async ({ page }) => {
    await signIn(page);
    await page.locator('.shell__tabs').getByRole('button', { name: 'Menu' }).click();
    const menu = page.getByRole('dialog', { name: 'Menu' });
    await menu.getByRole('link', { name: 'Greenhouse' }).click();
    await expect(menu).toBeHidden();
    await expect(page.getByRole('heading', { level: 1, name: 'Greenhouse' })).toBeVisible();
    await expect(page.locator('.pots')).toBeVisible();
    await expect(page.locator('.facilities')).toBeVisible();
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBeTruthy();
});
