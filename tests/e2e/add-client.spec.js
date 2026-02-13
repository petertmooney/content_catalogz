const { test, expect } = require('@playwright/test');

test('Add Client modal opens from dashboard (admin)', async ({ page }) => {
  // 1) Login as admin
  await page.goto('/admin/login.php');
  await page.fill('#username', 'admin');
  await page.fill('#password', 'admin_password');
  await page.click('.submit-btn');

  // Ensure we reach the dashboard
  await expect(page).toHaveURL(/.*\/admin\/dashboard.php/);
  await expect(page.locator('#section-dashboard')).toBeVisible();

  // 2) Open Add Client modal via data-action button
  const addBtn = page.locator('[data-action="openAddClientModal"]');
  await expect(addBtn).toBeVisible();
  await addBtn.click();

  // Modal should be visible, first input focused and measurable (non-zero width)
  const modal = page.locator('#addClientModal');
  await expect(modal).toHaveClass(/show/);
  const firstInput = page.locator('#newClientFirstName');
  await expect(firstInput).toBeFocused();
  const inputWidth = await firstInput.evaluate(el => el.offsetWidth || el.getBoundingClientRect().width || 0);
  expect(inputWidth).toBeGreaterThan(0);

  // Fill minimal required field and cancel (just verify UI)
  await page.fill('#newClientFirstName', 'E2E Test');
  await page.click('#addClientModal .close-btn');
  await expect(modal).not.toHaveClass(/show/);
});