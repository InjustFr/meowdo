import { expect, test } from '@playwright/test';
import { row, signIn, unique } from './support/session.js';

test('a task added on one device shows up on another when it comes back', async ({ browser }) => {
    const phone = await (await browser.newContext()).newPage();
    const laptop = await (await browser.newContext()).newPage();
    await signIn(phone);
    await signIn(laptop);

    const task = unique('Water the ferns');
    await phone.getByLabel('New task').fill(task);
    await phone.getByLabel('New task').press('Enter');
    await expect(row(phone, task)).toBeVisible();

    await expect(row(laptop, task)).toHaveCount(0);
    await laptop.evaluate(() => window.dispatchEvent(new Event('focus')));
    await expect(row(laptop, task)).toBeVisible();
});
