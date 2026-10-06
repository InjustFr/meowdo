import { expect, test } from '@playwright/test';
import { signIn } from './support/session.js';

test('the tab bar navigates on a phone', async ({ page }) => {
    await signIn(page);
    await expect(page.locator('.shell__strip .cat-desk')).toBeVisible();
    await page.locator('.shell__tabs').getByRole('link', { name: 'Matrix' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Matrix' })).toBeVisible();
    await page.locator('.shell__tabs').getByRole('link', { name: 'Projects' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Projects' })).toBeVisible();
});
