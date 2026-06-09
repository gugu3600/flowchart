import { test, expect } from '@playwright/test'

test.describe('Canvas', () => {
  test('renders canvas with sidebar and new flow button after login', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[type="email"]', 'admin@flowchart.dev')
    await page.fill('input[type="password"]', 'password')
    await page.click('button[type="submit"]')

    await page.waitForURL('**/canvas', { timeout: 10000 })

    await expect(page.locator('.user-profile')).toBeVisible({ timeout: 10000 })

    await expect(page.locator('.page-title')).toHaveText('Flowchart')
    await expect(page.locator('.sidebar')).toBeVisible({ timeout: 10000 })
    await expect(page.locator('.sidebar')).toContainText('Logic')
    await expect(page.locator('.sidebar')).toContainText('Folder / File')

    const flowTab = page.locator('.mode-tab:has-text("Flow")')
    await expect(flowTab).toBeVisible()
    await expect(flowTab).toHaveClass(/active/)

    const schemaTab = page.locator('.mode-tab:has-text("Schema")')
    await expect(schemaTab).toBeVisible()

    const newFlowBtn = page.locator('button:has-text("+ New Flow")')
    await expect(newFlowBtn).toBeVisible({ timeout: 10000 })
  })

  test('create a new flow and save', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[type="email"]', 'admin@flowchart.dev')
    await page.fill('input[type="password"]', 'password')
    await page.click('button[type="submit"]')

    await page.waitForURL('**/canvas', { timeout: 10000 })

    const newFlowBtn = page.locator('button:has-text("+ New Flow")')
    await expect(newFlowBtn).toBeVisible({ timeout: 10000 })

    await newFlowBtn.click()
    await page.fill('.flow-name-input', 'Test Flow')
    await page.click('button:has-text("Create")')

    await expect(page.locator('select.flow-select')).toBeVisible({ timeout: 10000 })

    await expect(page.locator('.user-profile')).toBeVisible({ timeout: 10000 })

    const saveBtn = page.locator('button:has-text("Save"):not([disabled])')
    await expect(saveBtn).toBeVisible({ timeout: 5000 })
    await saveBtn.click()

    const flowSelect = page.locator('select.flow-select')
    await expect(flowSelect).toHaveValue(/^\d+$/)
  })
})
