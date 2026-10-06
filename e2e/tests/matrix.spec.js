import { expect, test } from '@playwright/test';
import { row, signIn, unique } from './support/session.js';

test('a task sorted into Pounce moves above every other quadrant in today', async ({ page }) => {
    await signIn(page);
    const task = unique('Fix the leak');
    await page.getByLabel('New task').fill(task);
    await page.getByLabel('New task').press('Enter');

    await row(page, task).getByRole('button', { name: `More for “${task}”` }).click();
    await page.getByRole('menuitemradio', { name: /Pounce/ }).click();

    await expect(row(page, task)).toHaveClass(/task-row--do_first/);
    const order = await page.locator('.shell__main .task-list').first().locator('.task-row').evaluateAll(
        (rows) => rows.map((element) => ({ text: element.textContent, pounce: element.classList.contains('task-row--do_first') })),
    );
    const position = order.findIndex((entry) => entry.text.includes(task));
    expect(order.slice(0, position + 1).every((entry) => entry.pounce)).toBe(true);

    await page.locator('.shell__rail').getByRole('link', { name: 'Matrix' }).click();
    await expect(page.locator('.matrix__zone--do_first .task-row', { hasText: task })).toBeVisible();
});
