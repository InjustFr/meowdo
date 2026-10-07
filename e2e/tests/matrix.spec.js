import { expect, test } from '@playwright/test';
import { row, signIn, unique } from './support/session.js';

test('a task sorted into Water moves above every other quadrant in today', async ({ page }) => {
    await signIn(page);
    const task = unique('Fix the leak');
    await page.getByLabel('New task').fill(task);
    await page.getByLabel('New task').press('Enter');

    await row(page, task).getByRole('button', { name: `More for “${task}”` }).click();
    await page.getByRole('menuitemradio', { name: /Water/ }).click();

    await expect(row(page, task)).toHaveClass(/task-row--do_first/);
    const order = await page.locator('.shell__main .task-list').first().locator('.task-row').evaluateAll(
        (rows) => rows.map((element) => ({ text: element.textContent, first: element.classList.contains('task-row--do_first') })),
    );
    const position = order.findIndex((entry) => entry.text.includes(task));
    expect(order.slice(0, position + 1).every((entry) => entry.first)).toBe(true);

    await page.locator('.shell__rail').getByRole('link', { name: 'Matrix' }).click();
    await expect(page.locator('.matrix__zone--do_first .task-row', { hasText: task })).toBeVisible();
});

test('the matrix filters by project and plans tasks from its rows', async ({ page }) => {
    await signIn(page);
    const project = unique('Studio');
    const task = unique('Ink the fox');

    await page.locator('.shell__rail').getByRole('button', { name: 'New project' }).click();
    await page.getByRole('dialog').getByLabel('Name').fill(project);
    await page.getByRole('button', { name: 'Create project' }).click();
    await expect(page.getByRole('heading', { level: 1, name: project })).toBeVisible();
    await page.getByLabel('New task').fill(task);
    await page.getByLabel('New task').press('Enter');
    await row(page, task).getByRole('button', { name: `More for “${task}”` }).click();
    await page.getByRole('menuitemradio', { name: /Plant/ }).click();
    await expect(row(page, task)).toHaveClass(/task-row--schedule/);

    await page.locator('.shell__rail').getByRole('link', { name: 'Matrix' }).click();
    await expect(page.locator('.matrix__zone .task-row').filter({ hasNotText: project }).first()).toBeVisible();

    await page.getByRole('group', { name: 'Filter by project' }).getByRole('button', { name: project }).click();
    await expect(page).toHaveURL(/projects=/);
    await expect(page.locator('.matrix__zone--schedule .task-row', { hasText: task })).toBeVisible();
    await expect(page.locator('.matrix .task-row').filter({ hasNotText: project })).toHaveCount(0);

    const heights = await page.locator('.matrix__zone').evaluateAll((zones) => zones.map((zone) => zone.getBoundingClientRect().height));
    expect(new Set(heights).size).toBe(1);

    await row(page, task).getByRole('button', { name: 'Add to today' }).click();
    await expect(row(page, task).getByRole('button', { name: 'Remove from today' })).toBeVisible();

    await page.getByRole('button', { name: 'Show all' }).click();
    await expect(page).not.toHaveURL(/projects=/);
    await expect(page.locator('.matrix__zone .task-row').filter({ hasNotText: project }).first()).toBeVisible();
});
