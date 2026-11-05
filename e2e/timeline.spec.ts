import { test, expect } from '@playwright/test';
import { AUTH_FILE } from './helpers';

test.use({ storageState: AUTH_FILE.borrower });

test('pengguna bisa membuat postingan di timeline', async ({ page }) => {
  const pesan = `Post E2E ${Date.now()}`;

  await page.goto('/timeline_home');
  await page.locator('#composer-body').fill(pesan);
  await page.locator('#kirim-btn').click();

  // Web-first assertion: menunggu sampai postingan muncul di feed
  await expect(page.locator('#feed-panel').getByText(pesan)).toBeVisible();

  // Tetap ada setelah reload = benar-benar tersimpan di database
  await page.reload();
  await expect(page.locator('#feed-panel').getByText(pesan)).toBeVisible();
});
