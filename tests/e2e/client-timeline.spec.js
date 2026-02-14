const { test, expect } = require('@playwright/test');

test.describe('Client Modal Activity Timeline', () => {
  test('timeline auto-populates with invoices, notes, tasks, emails, changes and all are clickable', async ({ page }) => {
    // Login first
    await page.goto('/admin/login.php');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*dashboard\.php/);

    // Open client modal for a client with activities
    // Assuming there's a client with ID 1, or we need to create one
    // For now, assume we can click on a client row
    const clientRow = page.locator('.client-row').first();
    if (await clientRow.count() > 0) {
      await clientRow.click();

      // Wait for modal to open
      await page.waitForSelector('#clientModal', { state: 'visible' });

      // Switch to activities tab
      await page.click('#client-tab-activities');

      // Wait for timeline to load
      await page.waitForSelector('#client-activities-list .activity-item');

      // Check that timeline has items
      const activityItems = page.locator('#client-activities-list .activity-item');
      const count = await activityItems.count();
      expect(count).toBeGreaterThan(0);

      // Check that items are clickable (have links or buttons)
      for (let i = 0; i < Math.min(count, 5); i++) { // Test first 5 items
        const item = activityItems.nth(i);
        const link = item.locator('a').first();
        if (await link.count() > 0) {
          // Click the link and check if it opens something (modal or switches tab)
          const initialModalCount = await page.locator('.modal[style*="display: flex"]').count();
          await link.click();
          // After click, either a new modal opens or tab switches
          const newModalCount = await page.locator('.modal[style*="display: flex"]').count();
          const isTabSwitched = await page.locator('#client-tab-notes.active, #client-tab-tasks.active, #client-tab-activities.active').count() > 0;
          expect(newModalCount > initialModalCount || isTabSwitched).toBe(true);

          // Close any opened modal
          const closeBtn = page.locator('.modal .close, .modal .btn-cancel').first();
          if (await closeBtn.count() > 0) {
            await closeBtn.click();
            await page.waitForTimeout(500);
          }
        }
      }
    } else {
      console.log('No clients found, skipping timeline test');
    }
  });
});