// @ts-check
import { test, expect } from '@playwright/test';

test.describe('Frontend App', () => {
  test('loads the Vue app', async ({ page }) => {
    await page.goto('http://localhost:3000');

    // The Vue app root should exist
    await expect(page.locator('#app')).toBeVisible();
  });

  test('page title is set', async ({ page }) => {
    await page.goto('http://localhost:3000');

    const title = await page.title();
    expect(title).toBeTruthy();
  });
});
