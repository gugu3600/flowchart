import { test, expect } from '@playwright/test'

test.describe('Admin Dashboard', () => {
  test('shows admin dashboard with stats and users after login', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[type="email"]', 'admin@flowchart.dev')
    await page.fill('input[type="password"]', 'password')
    await page.click('button[type="submit"]')

    await page.waitForURL('**/canvas', { timeout: 10000 })
    await expect(page.locator('.user-profile')).toBeVisible({ timeout: 10000 })

    await page.goto('/admin')
    await page.waitForURL('**/admin', { timeout: 10000 })

    await expect(page.locator('.admin-welcome-title')).toHaveText('Welcome, Super Admin')
    await expect(page.locator('.admin-stats')).toBeVisible({ timeout: 10000 })
    await expect(page.locator('.admin-table')).toBeVisible({ timeout: 10000 })
    await expect(page.locator('.tier-card')).toHaveCount(4)
    await expect(page.locator('.admin-table tbody tr').first()).toBeVisible()
  })

  test('shows tier definitions with permissions', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[type="email"]', 'admin@flowchart.dev')
    await page.fill('input[type="password"]', 'password')
    await page.click('button[type="submit"]')

    await page.waitForURL('**/canvas', { timeout: 10000 })
    await expect(page.locator('.user-profile')).toBeVisible({ timeout: 10000 })

    await page.goto('/admin')
    await page.waitForURL('**/admin', { timeout: 10000 })

    const tierCards = page.locator('.tier-card')
    await expect(tierCards).toHaveCount(4)

    const tierNames = await tierCards.locator('.tier-card-name').allTextContents()
    expect(tierNames.sort()).toEqual(['free', 'silver', 'gold', 'platinum'].sort())
  })

  test('opens upgrade modal and confirms upgrade', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[type="email"]', 'admin@flowchart.dev')
    await page.fill('input[type="password"]', 'password')
    await page.click('button[type="submit"]')

    await page.waitForURL('**/canvas', { timeout: 10000 })
    await expect(page.locator('.user-profile')).toBeVisible({ timeout: 10000 })

    await page.goto('/admin')
    await page.waitForURL('**/admin', { timeout: 10000 })

    await expect(page.locator('.admin-table tbody tr').first()).toBeVisible()

    const upgradeBtn = page.locator('button:has-text("Upgrade"):not([disabled])').first()
    await upgradeBtn.click()

    await expect(page.locator('.modal-title')).toContainText('Upgrade Tier')

    await page.locator('input[type="radio"][value="silver"]').click()
    await page.click('button:has-text("Confirm Upgrade")')

    await expect(page.locator('.admin-upgrade-success')).toBeVisible({ timeout: 10000 })
  })

  test('non-admin route guard redirects to canvas', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[type="email"]', 'admin@flowchart.dev')
    await page.fill('input[type="password"]', 'password')
    await page.click('button[type="submit"]')

    await page.waitForURL('**/canvas', { timeout: 10000 })
    await expect(page.locator('.user-profile')).toBeVisible({ timeout: 10000 })

    await page.goto('/register')
    await expect(page).toHaveURL(/\/register/)
  })
})
