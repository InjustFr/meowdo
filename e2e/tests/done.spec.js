import { expect, test } from '@playwright/test';
import { row, signIn, unique } from './support/session.js';

test('a completed task appears on done under today and can be reopened there', async ({ page }) => {
    await signIn(page);
    const task = unique('Repot the fern');
    await page.getByLabel('New task').fill(task);
    await page.getByLabel('New task').press('Enter');
    const completed = page.waitForResponse((response) => response.url().endsWith('/complete') && response.ok());
    await row(page, task).getByRole('checkbox', { name: `Complete “${task}”` }).click();
    await completed;
    await expect(row(page, task).getByRole('checkbox', { name: `Reopen “${task}”` })).toBeVisible();

    await page.locator('.shell__rail').getByRole('link', { name: 'Done', exact: true }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Done' })).toBeVisible();
    const today = page.locator('.page-section', { has: page.getByRole('heading', { name: /^Today/ }) });
    await expect(today.locator('.task-row', { hasText: task })).toBeVisible();

    const reopened = page.waitForResponse((response) => response.url().endsWith('/reopen') && response.ok());
    await row(page, task).getByRole('checkbox', { name: `Reopen “${task}”` }).click();
    await reopened;
    await expect(row(page, task)).toHaveCount(0);

    await page.locator('.shell__rail').getByRole('link', { name: 'Today' }).click();
    await expect(row(page, task).getByRole('checkbox', { name: `Complete “${task}”` })).toBeVisible();
});

test('the phone menu leads to done and statistics', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await signIn(page);
    const tabs = page.locator('.shell__tabs');
    const menu = page.getByRole('dialog', { name: 'Menu' });
    await tabs.getByRole('button', { name: 'Menu' }).click();
    await menu.getByRole('link', { name: 'Done', exact: true }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Done' })).toBeVisible();
    await tabs.getByRole('button', { name: 'Menu' }).click();
    await menu.getByRole('link', { name: 'Statistics', exact: true }).click();
    await expect(menu).toBeHidden();
    await expect(page.getByRole('heading', { level: 1, name: 'Statistics' })).toBeVisible();
});
