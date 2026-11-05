import { test, expect, type Page } from '@playwright/test';
import { AUTH_FILE, BOOK_TITLE } from './helpers';

// Alur bisnis inti Alinea: peminjam mengajukan pinjam -> pemilik menerima -> buku tidak tersedia lagi.

/** Panggil endpoint JSON milik app dari dalam halaman (memakai sesi + CSRF yang sama dengan frontend). */
async function api(page: Page, method: string, url: string, body?: unknown) {
  return page.evaluate(
    async ({ method, url, body }) => {
      const res = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
        },
        body: body ? JSON.stringify(body) : undefined,
      });
      return { status: res.status, json: await res.json() };
    },
    { method, url, body },
  );
}

test('peminjam mengajukan pinjam, pemilik menerima, buku tidak tersedia lagi', async ({ browser }) => {
  const borrowerCtx = await browser.newContext({ storageState: AUTH_FILE.borrower });
  const ownerCtx = await browser.newContext({ storageState: AUTH_FILE.owner });
  const borrower = await borrowerCtx.newPage();
  const owner = await ownerCtx.newPage();

  try {
    // 1) Peminjam: ajukan pinjam lewat halaman /pinjam (memakai prompt() dan alert() bawaan browser)
    const alerts: string[] = [];
    borrower.on('dialog', async (dialog) => {
      if (dialog.type() === 'prompt') {
        await dialog.accept(dialog.message().includes('Berapa hari') ? '7' : 'Perpustakaan Kota Malang');
      } else {
        alerts.push(dialog.message());
        await dialog.accept();
      }
    });

    await borrower.goto('/pinjam');
    const card = borrower.locator('.book-card', { hasText: BOOK_TITLE });
    await card.getByRole('button', { name: 'Ajukan Pinjam' }).click();
    await expect.poll(() => alerts.join(' ')).toContain('Pengajuan berhasil dikirim');

    // 2) Pemilik: pengajuan masuk dengan status pending
    await owner.goto('/pinjam');
    const incoming = await api(owner, 'GET', '/transactions/incoming');
    const trx = incoming.json.find(
      (t: any) => t.book.judul === BOOK_TITLE && t.status === 'pending',
    );
    expect(trx, 'pengajuan pinjam harus muncul di daftar pemilik').toBeTruthy();
    expect(trx.titik_temu).toBe('Perpustakaan Kota Malang');

    // 3) Pemilik menerima pengajuan
    //    TODO: ganti dengan klik tombol "Terima" di dashboard setelah ada data-testid-nya.
    const accepted = await api(owner, 'PATCH', `/transactions/${trx.id}/status`, { status: 'accepted' });
    expect(accepted.status).toBe(200);

    // 4) Buku sudah dipinjam, hilang dari daftar yang tersedia
    await borrower.goto('/pinjam');
    await expect(borrower.locator('.book-card', { hasText: BOOK_TITLE })).toHaveCount(0);
  } finally {
    await borrowerCtx.close();
    await ownerCtx.close();
  }
});
