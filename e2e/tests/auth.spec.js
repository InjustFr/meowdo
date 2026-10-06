import { expect, test } from '@playwright/test';
import { DEMO, signIn } from './support/session.js';

test('a wrong password is refused', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/\/login$/);
    await page.getByLabel('Email').fill(DEMO.email);
    await page.getByLabel('Password').fill('not-the-password');
    await page.getByRole('button', { name: 'Sign in' }).click();
    await expect(page.getByRole('alert')).toHaveText('Incorrect email or password.');
});

test('signing in opens today with the cat at the desk', async ({ page }) => {
    await signIn(page);
    await expect(page.getByRole('heading', { level: 1, name: 'Today' })).toBeVisible();
    await expect(page.locator('.shell__desk .cat-desk__name')).toHaveText('Mochi');
});

test('forgot password always answers the same way', async ({ page }) => {
    await page.goto('/password/forgot');
    await page.getByLabel('Email').fill('nobody@meowdo.local');
    await page.getByRole('button', { name: 'Send the link' }).click();
    await expect(page.getByRole('status')).toContainText('If an account exists for nobody@meowdo.local');
});
