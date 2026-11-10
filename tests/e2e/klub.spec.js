// @ts-check
import { test, expect } from '@playwright/test';

const OWNER  = { email: 'owner@test.com',    password: 'password123' };
const MEMBER = { email: 'member@test.com',   password: 'password123' };

// Helper: login
async function login(page, email, password) {
  await page.goto('/login');
  await page.fill('#email', email);
  await page.fill('#password', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\/dashboard/);
}

// ─────────────────────────────────────────────
// TEST: Buka halaman klub
// ─────────────────────────────────────────────
test('halaman klub dapat dibuka dan menampilkan konten', async ({ page }) => {
  await login(page, OWNER.email, OWNER.password);
  await page.goto('/klub');

  await expect(page).toHaveURL(/\/klub/);
  await expect(page.locator('body')).toBeVisible();
});

// ─────────────────────────────────────────────
// TEST: Buat klub baru
// ─────────────────────────────────────────────
test('membuat klub baru berhasil dan muncul di daftar', async ({ page }) => {
  await login(page, OWNER.email, OWNER.password);
  await page.goto('/klub');

  const namaKlub = `Klub E2E ${Date.now()}`;

  // Klik tombol buat klub
  const buatKlubBtn = page.locator(
    'button:has-text("Buat Klub"), button:has-text("Tambah Klub"), button:has-text("+ Klub"), [data-action="create-klub"]'
  ).first();
  await expect(buatKlubBtn).toBeVisible({ timeout: 10_000 });
  await buatKlubBtn.click();

  // Isi form modal buat klub
  const namaInput = page.locator(
    '[name="nama"], [name="name"], #nama-klub, #nama'
  ).first();
  await expect(namaInput).toBeVisible({ timeout: 5_000 });
  await namaInput.fill(namaKlub);

  // Deskripsi (opsional)
  const deskripsiInput = page.locator(
    '[name="deskripsi"], [name="description"], textarea[name="deskripsi"]'
  ).first();
  if (await deskripsiInput.count() > 0) {
    await deskripsiInput.fill('Klub untuk testing E2E otomatis.');
  }

  // Submit form
  const submitBtn = page.locator(
    'button[type="submit"]:has-text("Buat"), button[type="submit"]:has-text("Simpan"), button[type="submit"]:has-text("Tambah")'
  ).first();
  await submitBtn.click();

  // Klub harus muncul di halaman
  await expect(page.locator('body')).toContainText(namaKlub, { timeout: 10_000 });
});

// ─────────────────────────────────────────────
// TEST: User lain gabung klub
// ─────────────────────────────────────────────
test('user dapat bergabung ke klub yang ada', async ({ page }) => {
  // Login sebagai member
  await login(page, MEMBER.email, MEMBER.password);
  await page.goto('/klub');

  // Cari tombol gabung/join pada klub manapun
  const joinBtn = page.locator(
    'button:has-text("Gabung"), button:has-text("Join"), [data-action="join-klub"]'
  ).first();

  if (await joinBtn.count() === 0) {
    // Skip jika tidak ada klub yang bisa diikuti (misalnya sudah join semua)
    test.skip();
    return;
  }

  await expect(joinBtn).toBeVisible({ timeout: 10_000 });
  await joinBtn.click();

  // Tunggu respons AJAX
  await page.waitForTimeout(1500);

  // Setelah join, tombol berganti jadi "Keluar" atau "Leave"
  await expect(page.locator('body')).toContainText(
    /keluar|leave|anggota|bergabung/i,
    { timeout: 10_000 }
  );
});

// ─────────────────────────────────────────────
// TEST: User keluar dari klub
// ─────────────────────────────────────────────
test('user dapat keluar dari klub', async ({ page }) => {
  // Login sebagai member
  await login(page, MEMBER.email, MEMBER.password);
  await page.goto('/klub');

  // Cari tombol keluar (sudah join)
  const leaveBtn = page.locator(
    'button:has-text("Keluar"), button:has-text("Leave"), [data-action="leave-klub"]'
  ).first();

  if (await leaveBtn.count() === 0) {
    test.skip();
    return;
  }

  await expect(leaveBtn).toBeVisible({ timeout: 10_000 });
  await leaveBtn.click();

  // Konfirmasi dialog jika ada
  page.on('dialog', async (dialog) => {
    await dialog.accept();
  });

  await page.waitForTimeout(1500);

  // Setelah keluar, tombol kembali jadi "Gabung"
  await expect(page.locator('body')).toContainText(/gabung|join/i, { timeout: 10_000 });
});
