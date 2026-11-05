import { test as setup, expect } from '@playwright/test';
import { AUTH_FILE, PASSWORD, USERS } from './helpers';
import { LoginPage } from './pages/LoginPage';

// Login lewat UI satu kali per role, lalu sesinya dipakai ulang oleh semua spec.

for (const role of ['owner', 'borrower'] as const) {
  setup(`login sebagai ${role}`, async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login(USERS[role].email, PASSWORD);
    await login.expectLoggedIn();
    await page.context().storageState({ path: AUTH_FILE[role] });
  });
}

setup('login sebagai admin (panel Filament)', async ({ page }) => {
  await page.goto('/admin/login');
  await page.getByLabel(/email/i).fill(USERS.admin.email);
  await page.getByRole('textbox', { name: /password|kata sandi/i }).fill(PASSWORD);
  await page.getByRole('button', { name: /sign in|masuk/i }).click();
  await expect(page).toHaveURL(/\/admin\/?$/);
  await page.context().storageState({ path: AUTH_FILE.admin });
});
