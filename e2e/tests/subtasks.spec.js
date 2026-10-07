import { expect, test } from '@playwright/test';
import { row, signIn, unique } from './support/session.js';

test('a drawing split into steps completes once every step is done', async ({ page }) => {
    await signIn(page);
    const project = unique('Drawings');
    const drawing = unique('Fox drawing');
    const sketch = unique('Sketch');
    const colouring = unique('Colouring');

    await page.locator('.shell__rail').getByRole('button', { name: 'New project' }).click();
    await page.getByRole('dialog').getByLabel('Name').fill(project);
    await page.getByRole('button', { name: 'Create project' }).click();
    await expect(page.getByRole('heading', { level: 1, name: project })).toBeVisible();
    await page.getByLabel('New task').fill(drawing);
    await page.getByLabel('New task').press('Enter');

    await row(page, drawing).getByRole('button', { name: drawing, exact: true }).click();
    const editor = page.getByRole('dialog');
    for (const step of [sketch, colouring]) {
        await editor.getByLabel('New subtask').fill(step);
        await editor.getByLabel('New subtask').press('Enter');
        await expect(editor.getByRole('checkbox', { name: `Complete “${step}”` })).toBeVisible();
    }
    await expect(editor).toBeVisible();
    await editor.getByRole('button', { name: 'Cancel' }).click();
    await expect(editor).toBeHidden();

    await expect(row(page, drawing)).toContainText('0/2');
    await expect(row(page, drawing).getByRole('checkbox', { name: `“${drawing}” completes once its subtasks are done` })).toBeDisabled();
    await expect(page.locator('.task-list__subtasks .task-row')).toHaveCount(2);

    for (const step of [sketch, colouring]) {
        const completed = page.waitForResponse((response) => response.url().endsWith('/complete') && response.ok());
        await row(page, step).getByRole('checkbox', { name: `Complete “${step}”` }).click();
        await completed;
    }

    const done = page.locator('.shell__main .task-row--done', { has: page.locator('.task-row__title', { hasText: drawing }) });
    await expect(done).toContainText('2/2');
    await expect(done.locator('.drop-check')).toHaveAttribute('aria-checked', 'true');
    await expect(page.locator('.task-list__subtasks .task-row--done')).toHaveCount(2);
});

test('steps are added from the task menu, dragged onto a task and made tasks again', async ({ page }) => {
    await signIn(page);
    const project = unique('Comics');
    const page1 = unique('Page one');
    const ink = unique('Ink');
    const sketch = unique('Sketch');
    const letters = unique('Letters');

    await page.locator('.shell__rail').getByRole('button', { name: 'New project' }).click();
    await page.getByRole('dialog').getByLabel('Name').fill(project);
    await page.getByRole('button', { name: 'Create project' }).click();
    await expect(page.getByRole('heading', { level: 1, name: project })).toBeVisible();
    for (const title of [page1, ink]) {
        await page.getByLabel('New task').fill(title);
        await page.getByLabel('New task').press('Enter');
        await expect(row(page, title)).toBeVisible();
    }
    const subtasks = page.locator('.task-list__subtasks .task-row');

    await row(page, page1).getByRole('button', { name: `More for “${page1}”` }).click();
    await page.getByRole('menuitem', { name: 'Add a subtask' }).click();
    await page.getByLabel('New subtask').fill(sketch);
    await page.getByLabel('New subtask').press('Enter');
    await expect(subtasks.filter({ hasText: sketch })).toBeVisible();
    await page.getByLabel('New subtask').press('Escape');
    await expect(page.getByLabel('New subtask')).toBeHidden();

    await page.locator('.task-list__subtasks').getByRole('button', { name: 'Add a subtask' }).click();
    await page.getByLabel('New subtask').fill(letters);
    await page.getByLabel('New subtask').press('Enter');
    await expect(subtasks).toHaveCount(2);
    await expect(row(page, page1)).toContainText('0/2');

    await row(page, ink).dragTo(row(page, page1).getByRole('button', { name: page1, exact: true }));
    await expect(subtasks.filter({ hasText: ink })).toBeVisible();
    await expect(row(page, page1)).toContainText('0/3');

    await subtasks.filter({ hasText: ink }).getByRole('button', { name: `More for “${ink}”` }).click();
    await page.getByRole('menuitem', { name: 'Make it a task' }).click();
    await expect(subtasks).toHaveCount(2);
    await expect(row(page, ink)).toBeVisible();
    await expect(row(page, page1)).toContainText('0/2');
});
