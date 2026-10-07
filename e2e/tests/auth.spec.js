import { expect, test } from '@playwright/test';
import { signIn, signInWithAccount, unique } from './support/session.js';

test('signed-out visitors sign in with their mossyleaf account', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/\/authorize\?/);
    await signInWithAccount(page, 'demo');
    await page.waitForURL('/');
    await expect(page.getByRole('heading', { level: 1, name: 'Today' })).toBeVisible();
    await expect(page.locator('.shell__rail .player-progress')).toBeVisible();
});

test('a new mossyleaf account starts with an empty herbarium', async ({ page }) => {
    const account = unique('fern').replace(' ', '-');
    await page.goto('/login');
    await signInWithAccount(page, account, { email: `${account}@mossyleaf.test`, name: 'Fern' });
    await page.waitForURL('/');
    await expect(page.locator('.shell__rail [data-test=species]')).toHaveText('0 of 48 mosses');
    await page.goto('/settings');
    await expect(page.getByText(`${account}@mossyleaf.test`)).toBeVisible();
});

test('signing out also signs out of the mossyleaf account', async ({ page }) => {
    await signIn(page);
    await page.goto('/settings');
    await page.getByRole('button', { name: 'Sign out' }).click();
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page).toHaveURL(/\/authorize\?/);
});
