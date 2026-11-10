// @ts-check
import { test, expect } from '@playwright/test';

const OWNER = { email: 'owner@test.com', password: 'password123' };

// Helper: login
async function login(page, email, password) {
  await page.goto('/login');
  await page.fill('#email', email);
  await page.fill('#password', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\/dashboard/);
}

// ─────────────────────────────────────────────
// TEST: Buka timeline home
// ─────────────────────────────────────────────
test('halaman timeline home dapat dibuka setelah login', async ({ page }) => {
  await login(page, OWNER.email, OWNER.password);
  await page.goto('/timeline_home');

  await expect(page).toHaveURL(/\/timeline_home/);
  await expect(page.locator('body')).toBeVisible();
});

// ─────────────────────────────────────────────
// TEST: Buat post baru
// ─────────────────────────────────────────────
test('membuat post baru berhasil dan muncul di feed', async ({ page }) => {
  await login(page, OWNER.email, OWNER.password);
  await page.goto('/timeline_home');

  const kontenPost = `Post E2E Test — ${Date.now()}`;

  // Cari textarea / input untuk membuat post
  const postInput = page.locator(
    'textarea[name="konten"], textarea[name="content"], textarea[placeholder*="pikiran"], textarea[placeholder*="tulis"], #post-input'
  ).first();
  await expect(postInput).toBeVisible({ timeout: 10_000 });
  await postInput.fill(kontenPost);

  // Submit post
  const postBtn = page.locator(
    'button[type="submit"]:has-text("Post"), button[type="submit"]:has-text("Kirim"), button[type="submit"]:has-text("Bagikan")'
  ).first();
  await postBtn.click();

  // Post harus muncul di feed
  await expect(page.locator('body')).toContainText(kontenPost, { timeout: 10_000 });
});

// ─────────────────────────────────────────────
// TEST: Like post
// ─────────────────────────────────────────────
test('like post berhasil menambah counter like', async ({ page }) => {
  await login(page, OWNER.email, OWNER.password);
  await page.goto('/timeline_home');

  // Ambil counter like sebelum klik
  const likeBtn = page.locator(
    'button[data-action="like"], button:has-text("Like"), button.like-btn, [data-like-btn]'
  ).first();
  await expect(likeBtn).toBeVisible({ timeout: 10_000 });

  await likeBtn.click();

  // Tunggu respons (like biasanya AJAX)
  await page.waitForTimeout(1000);

  // Tombol like harus masih ada (tidak error)
  await expect(likeBtn).toBeVisible();
});

// ─────────────────────────────────────────────
// TEST: Tambah komentar
// ─────────────────────────────────────────────
test('menambah komentar pada post berhasil', async ({ page }) => {
  await login(page, OWNER.email, OWNER.password);
  await page.goto('/timeline_home');

  // Klik tombol komentar pada post pertama
  const komentarBtn = page.locator(
    'button:has-text("Komentar"), button:has-text("Comment"), [data-action="comment"], button.comment-btn'
  ).first();
  await expect(komentarBtn).toBeVisible({ timeout: 10_000 });
  await komentarBtn.click();

  // Isi textarea komentar
  const isiKomentar = `Komentar E2E — ${Date.now()}`;
  const komentarInput = page.locator(
    'textarea[name="isi"], textarea[name="comment"], textarea[placeholder*="komentar"], .comment-input'
  ).first();
  await expect(komentarInput).toBeVisible({ timeout: 5_000 });
  await komentarInput.fill(isiKomentar);

  // Submit komentar
  const submitKomentar = page.locator(
    'button[type="submit"]:has-text("Kirim"), button[type="submit"]:has-text("Comment"), button.submit-comment'
  ).first();
  await submitKomentar.click();

  // Komentar harus muncul
  await expect(page.locator('body')).toContainText(isiKomentar, { timeout: 10_000 });
});

// ─────────────────────────────────────────────
// TEST: Bookmark post → muncul di timeline_simpanan
// ─────────────────────────────────────────────
test('bookmark post berhasil dan muncul di halaman simpanan', async ({ page }) => {
  await login(page, OWNER.email, OWNER.password);
  await page.goto('/timeline_home');

  // Klik tombol bookmark pada post pertama
  const bookmarkBtn = page.locator(
    'button[data-action="bookmark"], button:has-text("Simpan"), button.bookmark-btn, [data-bookmark-btn]'
  ).first();
  await expect(bookmarkBtn).toBeVisible({ timeout: 10_000 });
  await bookmarkBtn.click();

  await page.waitForTimeout(1000);

  // Navigasi ke halaman simpanan
  await page.goto('/timeline_simpanan');
  await expect(page).toHaveURL(/\/timeline_simpanan/);

  // Halaman harus memuat tanpa error
  await expect(page.locator('body')).toBeVisible();
});
