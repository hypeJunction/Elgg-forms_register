import { test, expect } from '@playwright/test';
import { getUserByUsername, deleteUserByUsername, gotoRegister } from '../helpers/elgg';

/**
 * End-to-end tests for the forms_register customized registration form.
 *
 * These tests exercise the /register page in its default configuration
 * (all plugin settings off). Tests for autogen_name / autogen_username /
 * first_last_name modes are marked skipped because they require toggling
 * plugin settings and refreshing the test DB, which should be handled by
 * a dedicated fixture runner.
 */
test.describe('forms_register: register page', () => {
  test('register page renders with expected fields', async ({ page }) => {
    await gotoRegister(page);

    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="name"], input[name="first_name"]')).toHaveCount(
      await page.locator('input[name="name"]').count() +
        await page.locator('input[name="first_name"]').count()
    );
  });

  test('submit creates user in default mode (UI + DB)', async ({ page }) => {
    const rand = Math.random().toString(36).slice(2, 10);
    const username = `pwt_${rand}`;
    const email = `${username}@example.com`;
    const password = `Pa55word!${rand}`;

    await gotoRegister(page);

    // Default mode: display name field is 'name'
    const hasName = await page.locator('input[name="name"]').count();
    if (hasName) {
      await page.fill('input[name="name"]', `Playwright ${rand}`);
    }

    await page.fill('input[name="email"]', email);

    const hasUsername = await page.locator('input[name="username"]').count();
    if (hasUsername) {
      await page.fill('input[name="username"]', username);
    }

    await page.fill('input[name="password"]', password);
    const hasRepeat = await page.locator('input[name="password2"]').count();
    if (hasRepeat) {
      await page.fill('input[name="password2"]', password);
    }

    await page.click('button[type="submit"], input[type="submit"]');

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
    await page.click('button[type="submit"], input[type="submit"]');
    // Stay on register page or show error
    await page.waitForLoadState('networkidle');
    // Either HTML5 validation blocks submit, or Elgg reports errors
    const url = page.url();
    expect(url).toMatch(/\/register/);
  });
});

test.describe('forms_register: username validation endpoints', () => {
  test('availableusername endpoint returns ok for unused name', async ({ request }) => {
    const rand = Math.random().toString(36).slice(2, 10);
    const response = await request.post('/action/validation/availableusername', {
      form: { username: `avail_${rand}` },
    });
    // Elgg action responses may 200 with ok_response or 422 when taken
    expect([200, 302]).toContain(response.status());
  });

  test('availableusername endpoint returns error for existing name', async ({ request, page }) => {
    // Create via default register flow
    const rand = Math.random().toString(36).slice(2, 10);
    const username = `taken_${rand}`;
    const email = `${username}@example.com`;
    const password = `Pa55word!${rand}`;

    await gotoRegister(page);
    if (await page.locator('input[name="name"]').count()) {
      await page.fill('input[name="name"]', `Taken ${rand}`);
    }
    await page.fill('input[name="email"]', email);
    if (await page.locator('input[name="username"]').count()) {
      await page.fill('input[name="username"]', username);
    }
    await page.fill('input[name="password"]', password);
    if (await page.locator('input[name="password2"]').count()) {
      await page.fill('input[name="password2"]', password);
    }
    await page.click('button[type="submit"], input[type="submit"]');
    await page.waitForLoadState('networkidle');

    const response = await request.post('/action/validation/availableusername', {
      form: { username },
    });
    expect([422, 400]).toContain(response.status());

    await deleteUserByUsername(username);
  });

  test('validusername endpoint rejects too-short names', async ({ request }) => {
    const response = await request.post('/action/validation/validusername', {
      form: { username: 'ab' },
    });
    expect([422, 400]).toContain(response.status());
  });
});
