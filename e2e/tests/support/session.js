export const DEMO = { account: 'demo' };

export async function signInWithAccount(page, account, claims = null) {
    await page.locator('input[name="username"]').fill(account);
    if (claims) await page.locator('textarea[name="claims"]').fill(JSON.stringify(claims));
    await page.getByRole('button', { name: 'Sign-in' }).click();
}

export async function signIn(page, account = DEMO) {
    await page.goto('/login');
    await signInWithAccount(page, account.account);
    await page.waitForURL('/');
}

export const unique = (label) => `${label} ${Date.now().toString(36)}${Math.random().toString(36).slice(2, 5)}`;

export function row(page, title) {
    return page.locator('.shell__main .task-row', { hasText: title });
}
