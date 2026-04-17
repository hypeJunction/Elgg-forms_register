import { test, expect } from '@playwright/test';
import { getUserByUsername, deleteUserByUsername, gotoRegister } from '../helpers/elgg';

/**
 * End-to-end tests for the forms_register customized registration form.
 *
 * Uses the `.elgg-form-register` scope to avoid strict-mode violations from
 * the header login dropdown which also contains password/username inputs.
 *
 * API tests for the validation endpoints first navigate to the register page
 * to obtain a valid CSRF token (Elgg redirects unauthenticated raw POSTs).
 */
test.describe('forms_register: register page', () => {
  test('register page renders with expected fields', async ({ page }) => {
    await gotoRegister(page);
    const form = page.locator('.elgg-form-register');

    await expect(form.locator('input[name="email"]')).toBeVisible();
    await expect(form.locator('input[name="password"]')).toBeVisible();
    // Display name or first_name
    const nameCount =
      (await form.locator('input[name="name"]').count()) +
      (await form.locator('input[name="first_name"]').count());
    expect(nameCount).toBeGreaterThan(0);
  });

  test('submit creates user in default mode (UI + DB)', async ({ page }) => {
    const rand = Math.random().toString(36).slice(2, 10);
    const username = `pwt_${rand}`;
    const email = `${username}@example.com`;
    const password = `Pa55word!${rand}`;

    await gotoRegister(page);
    const form = page.locator('.elgg-form-register');

    if (await form.locator('input[name="name"]').count()) {
      await form.locator('input[name="name"]').fill(`Playwright ${rand}`);
    }

    await form.locator('input[name="email"]').fill(email);

    if (await form.locator('input[name="username"]').count()) {
      await form.locator('input[name="username"]').fill(username);
    }

    await form.locator('input[name="password"]').fill(password);
    if (await form.locator('input[name="password2"]').count()) {
      await form.locator('input[name="password2"]').fill(password);
    }

    await form.locator('button[type="submit"], input[type="submit"]').click();

    // Assert UI: redirected away from /register
    await page.waitForLoadState('networkidle');
    expect(page.url()).not.toMatch(/\/register(\?|$)/);

    // Assert DB: user exists
    const user = await getUserByUsername(username);
    expect(user).not.toBeNull();
    expect(user.email).toBe(email);

    // Cleanup
    await deleteUserByUsername(username);
  });

  test('register page shows errors when required fields missing', async ({ page }) => {
    await gotoRegister(page);
    const form = page.locator('.elgg-form-register');
    await form.locator('button[type="submit"], input[type="submit"]').click();
    // Stay on register page or show error
    await page.waitForLoadState('networkidle');
    const url = page.url();
    expect(url).toMatch(/\/register/);
  });
});

test.describe('forms_register: username validation endpoints', () => {
  /**
   * Helper: get CSRF token from the register page.
   * Elgg requires a valid __elgg_token + __elgg_ts pair for all action POSTs.
   */
  async function getCsrfTokens(page: any): Promise<{ token: string; ts: string }> {
    await page.goto('/register');
    const token = await page.locator('input[name="__elgg_token"]').first().inputValue();
    const ts = await page.locator('input[name="__elgg_ts"]').first().inputValue();
    return { token, ts };
  }

  test('availableusername endpoint returns ok for unused name', async ({ page }) => {
    const { token, ts } = await getCsrfTokens(page);
    const rand = Math.random().toString(36).slice(2, 10);

    const response = await page.request.post('/action/validation/availableusername', {
      form: { username: `avail_${rand}`, __elgg_token: token, __elgg_ts: ts },
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    expect([200]).toContain(response.status());
  });

  test('availableusername endpoint returns error for existing name', async ({ page }) => {
    const rand = Math.random().toString(36).slice(2, 10);
    const username = `taken_${rand}`;
    const email = `${username}@example.com`;
    const password = `Pa55word!${rand}`;

    // Navigate to register and get tokens BEFORE submitting the form.
    await gotoRegister(page);
    const form = page.locator('.elgg-form-register');
    if (await form.locator('input[name="name"]').count()) {
      await form.locator('input[name="name"]').fill(`Taken ${rand}`);
    }
    await form.locator('input[name="email"]').fill(email);
    if (await form.locator('input[name="username"]').count()) {
      await form.locator('input[name="username"]').fill(username);
    }
    await form.locator('input[name="password"]').fill(password);
    if (await form.locator('input[name="password2"]').count()) {
      await form.locator('input[name="password2"]').fill(password);
    }
    await form.locator('button[type="submit"], input[type="submit"]').click();
    await page.waitForLoadState('networkidle');

    // After registration, user is logged in — log out so /register is accessible.
    await page.goto('/action/logout');
    await page.waitForLoadState('networkidle');

    // Get fresh tokens from the register page and test the endpoint.
    const { token, ts } = await getCsrfTokens(page);
    const response = await page.request.post('/action/validation/availableusername', {
      form: { username, __elgg_token: token, __elgg_ts: ts },
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    expect([422, 400]).toContain(response.status());

    await deleteUserByUsername(username);
  });

  test('validusername endpoint rejects too-short names', async ({ page }) => {
    const { token, ts } = await getCsrfTokens(page);

    const response = await page.request.post('/action/validation/validusername', {
      form: { username: 'ab', __elgg_token: token, __elgg_ts: ts },
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    expect([422, 400]).toContain(response.status());
  });
});
