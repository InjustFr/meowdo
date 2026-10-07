export const DEMO = { email: 'demo@mossydew.local', password: 'mossydewmossydew' };

export async function signIn(page, account = DEMO) {
    await page.goto('/login');
    await page.getByLabel('Email').fill(account.email);
    await page.getByLabel('Password').fill(account.password);
    await page.getByRole('button', { name: 'Sign in' }).click();
    await page.waitForURL('/');
}

export const unique = (label) => `${label} ${Date.now().toString(36)}${Math.random().toString(36).slice(2, 5)}`;

export function row(page, title) {
    return page.locator('.shell__main .task-row', { hasText: title });
}
