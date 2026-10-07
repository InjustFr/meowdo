import { expect, test } from '@playwright/test';
import { row, signIn, unique } from './support/session.js';

test('completing a weekly task plans its next occurrence a week later', async ({ page }) => {
    await signIn(page);
    const task = unique('Water the ferns');
    await page.getByLabel('New task').fill(task);
    await page.getByLabel('New task').press('Enter');

    await row(page, task).getByRole('button', { name: task, exact: true }).click();
    const editor = page.getByRole('dialog');
    await editor.getByRole('group', { name: 'Repeat' }).getByRole('combobox').click();
    await page.getByRole('option', { name: 'week', exact: true }).click();
    await expect(editor.getByLabel('Number of days, weeks, months or years')).toHaveValue('1');
    await expect(editor).toContainText('Every week');
    await editor.getByRole('button', { name: 'Save' }).click();
    await expect(editor).toBeHidden();
    await expect(row(page, task)).toContainText('Every week');

    const completed = page.waitForResponse((response) => response.url().endsWith('/complete') && response.ok());
    await row(page, task).getByRole('checkbox', { name: `Complete “${task}”` }).click();
    await completed;
    await expect(row(page, task).getByRole('checkbox', { name: `Reopen “${task}”` })).toBeVisible();

    await page.locator('.shell__rail').getByRole('link', { name: 'Upcoming' }).click();
    await page.getByRole('button', { name: 'Next week' }).click();
    const nextWeek = page.locator('.upcoming__day').first().locator('.task-row', { hasText: task });
    await expect(nextWeek).toBeVisible();
    await expect(nextWeek).toContainText('Every week');
    await expect(nextWeek.getByRole('checkbox', { name: `Complete “${task}”` })).toBeVisible();
});
