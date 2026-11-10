// @ts-check
import { test, expect } from '@playwright/test';

const BASE = process.env.PW_BASE_URL ?? 'http://localhost:8000';

// Credentials yang sudah di-seed oleh E2eSeeder
const OWNER = { email: 'owner@test.com', password: 'password123' };

// Helper: login via form
async function login(page, email, password) {
  await page.goto('/login');
  await page.fill('#email', email);
  await page.fill('#password', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\/dashboard/);
}

// ─────────────────────────────────────────────
// TEST: Register
// ─────────────────────────────────────────────
test('register akun baru berhasil', async ({ page }) => {
  const unique = Date.now();

  await page.goto('/daftar_akun');
  await page.fill('[name="name"]', `User Test ${unique}`);
  await page.fill('[name="username"]', `usertest${unique}`);
  await page.fill('[name="email"]', `usertest${unique}@example.com`);
  await page.fill('[name="password"]', 'password123');
  await page.fill('[name="password_confirmation"]', 'password123');
  await page.fill('[name="kota"]', 'Jakarta');
  await page.fill('[name="no_telp"]', '081200000001');

  // Pilih minimal 1 genre jika ada checkbox genre
  const genreCheckbox = page.locator('input[name="genres[]"]').first();
  if (await genreCheckbox.count() > 0) {
    await genreCheckbox.check();
  }

  await page.click('button[type="submit"]');

  // Setelah register, redirect ke /dashboard
  await expect(page).toHaveURL(/\/dashboard/);
  await expect(page.locator('body')).toBeVisible();
});

// ─────────────────────────────────────────────
// TEST: Login berhasil
// ─────────────────────────────────────────────
test('login dengan kredensial valid berhasil', async ({ page }) => {
  await page.goto('/login');
  await page.fill('#email', OWNER.email);
  await page.fill('#password', OWNER.password);
  await page.click('button[type="submit"]');

  await expect(page).toHaveURL(/\/dashboard/);
});

// ─────────────────────────────────────────────
// TEST: Login gagal
// ─────────────────────────────────────────────
test('login dengan password salah tampil error', async ({ page }) => {
  await page.goto('/login');
  await page.fill('#email', OWNER.email);
  await page.fill('#password', 'password-salah-123');
  await page.click('button[type="submit"]');

  // Tetap di halaman login atau tampil pesan error
  await expect(page).toHaveURL(/\/login/);
  // Pesan error dari Laravel: "Email atau password salah"
  await expect(page.locator('body')).toContainText(/salah|email|password/i);
});

// ─────────────────────────────────────────────
// TEST: Logout
// ─────────────────────────────────────────────
test('logout berhasil redirect ke halaman utama', async ({ page }) => {
  await login(page, OWNER.email, OWNER.password);

  // Klik tombol logout (form POST /logout)
  await page.click('button[form="logout-form"], form[action*="logout"] button, [data-action="logout"]');

  await expect(page).toHaveURL(/^http:\/\/localhost:8000\/?$/);
});
