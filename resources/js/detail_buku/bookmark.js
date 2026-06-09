import { showToast } from './utils';

export async function initBookmarkState() {
  if (!window.__AUTH__) {
    return;
  }

  const book = window.__BOOK_DATA__;
  const identifier = book.id;
  const identifierType = book.book_identifier_type;

  try {
    const res = await fetch (
      `/api/bookmarks/check?book_identifier=${encodeURIComponent(identifier)}&identifier_type=${encodeURIComponent(identifierType)}`,
      {headers: {'Accept': 'application/json'}}
    );
    const data = await res.json();

    const btn = document.getElementById('simpanBtn');
    if (data.bookmarked) {
      if (btn) btn.classList.add('saved');
    } else {
      if (btn) btn.classList.remove('saved');
    }
  } catch (e) {
    console.error('Gagal cek status bookmark: ', e);
  }
}

export function initBookmarkEvents() {
  document.getElementById('simpanBtn')?.addEventListener('click', async () => {
    if (!window.__AUTH__) {
      showToast('Kamu harus login untuk menyimpan buku.', 'info');
      return;
    }
  
    const book = window.__BOOK_DATA__;
    const btn = document.getElementById('simpanBtn');
    const isCurrentlySaved = btn.classList.contains('saved');
  
    btn.classList.toggle('saved');
  
    try {
      const res = await fetch('/api/bookmarks', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
          book_identifier: String(book.id),
          identifier_type: book.book_identifier_type,
          judul: book.judul,
          penulis: book.penulis ?? '',
          foto_sampul: book.foto_sampul ?? null,
          kategori: book.kategori ?? '',
        }),
      });
  
      const data = await res.json();
  
      if (!res.ok) {
        throw new Error(data.message ?? 'Gagal');
      }
  
      if (data.bookmarked) {
        btn.classList.add('saved');
        showToast('Buku disimpan di Dasbor', 'success');
      } else {
        btn.classList.remove('saved');
        showToast('Buku dihapus dari Dasbor', 'info');
      }
    } catch (e) {
      if (isCurrentlySaved) {
        btn.classList.add('saved');
      } else {
        btn.classList.remove('saved');
        showToast('Gagal menyimpan buku. Coba lagi.', 'error');
      }
    }
  });
}
