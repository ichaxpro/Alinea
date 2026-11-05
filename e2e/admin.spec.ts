import { test, expect } from '@playwright/test';
import { AUTH_FILE, USERS } from './helpers';

test.describe('Panel admin (Filament)', () => {
  test.describe('sebagai admin', () => {
    test.use({ storageState: AUTH_FILE.admin });

    test('admin bisa membuka dashboard dan daftar pengguna', async ({ page }) => {
      await page.goto('/admin');
      await expect(page.getByText('Alinea Admin').first()).toBeVisible();

      await page.goto('/admin/users');
      await expect(page.getByText(USERS.borrower.email)).toBeVisible();
    });
  });

  test.describe('sebagai pengguna biasa', () => {
    test.use({ storageState: AUTH_FILE.borrower });

    test('pengguna biasa ditolak masuk panel admin', async ({ page }) => {
      const res = await page.goto('/admin');
      expect(res?.status()).toBe(403);
    });
  });
});
