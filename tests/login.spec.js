import { test, expect } from '@playwright/test'

test.describe('Login Flow', () => {
  test('shows login page and logs in with valid credentials', async ({ page }) => {
    await page.goto('/login')

    await expect(page.locator('h1')).toHaveText('Welcome Back')

    await page.fill('input[type="email"]', 'admin@flowchart.dev')
    await page.fill('input[type="password"]', 'password')

    await page.click('button[type="submit"]')

    await page.waitForURL('**/canvas', { timeout: 10000 })
    expect(page.url()).toContain('/canvas')
  })

  test('shows error on invalid credentials', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[type="email"]', 'wrong@email.com')
    await page.fill('input[type="password"]', 'wrongpass')

    await page.click('button[type="submit"]')

    await expect(page.locator('.error-msg')).toBeVisible({ timeout: 10000 })
    await expect(page.locator('.error-msg')).not.toBeEmpty()
  })
})
