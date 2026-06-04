// @ts-check
import { test, expect } from '@playwright/test';

test.describe('App Setup & Health', () => {
  test('Vue app mounts and renders #app', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('#app')).toBeAttached();
  });

  test('page has a valid title', async ({ page }) => {
    await page.goto('/');
    const title = await page.title();
    expect(title).toBeTruthy();
  });

  test('no console errors on load', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (err) => errors.push(err.message));

    await page.goto('/');
    await page.waitForLoadState('networkidle');

    expect(errors).toHaveLength(0);
  });
});

test.describe('Environment', () => {
  test('vite dev server responds on port 3000', async ({ request }) => {
    const resp = await request.get('/');
    expect(resp.ok()).toBeTruthy();
  });
});
