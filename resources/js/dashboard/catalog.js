import { apiCall } from './api.js';
import { $, toast, getInitial } from './utils.js';
import { state } from './state.js';
import { renderSidebarProfile, updateSidebarStats } from './profile.js';
import { hideSearchResults, hideBookPreview } from './book-search.js';

const CATALOG_PER_PAGE = 10;

export async function loadCatalog() {
  try {
    state.catalogData = await apiCall('GET', '/personal-books');
    state.catalogLoaded = true;
    renderCatalog();
    updateSidebarStats();
  } catch (err) {
    toast('Gagal memuat koleksi', 'error');
  }
}

export function renderCatalog() {
  const list = $('#catalog-list');
  if (!list) return;

  const filtered = state.catalogData.filter(b =>
    !state.catalogSearch || b.judul.toLowerCase().includes(state.catalogSearch.toLowerCase()) || b.penulis.toLowerCase().includes(state.catalogSearch.toLowerCase())
  );

  $('#catalog-count').textContent = `${filtered.length} buku`;

  if (filtered.length === 0) {
    list.innerHTML = `<div class="text-center py-16"><div class="text-4xl mb-3"></div><p class="text-sm text-gray-400 font-medium">${state.catalogSearch ? 'Tidak ada buku ditemukan.' : 'Koleksimu masih kosong. Tambahkan buku pertamamu!'}</p></div>`;
    return;
  }

  const totalPages = Math.max(1, Math.ceil(filtered.length / CATALOG_PER_PAGE));
  if (state.catalogPage > totalPages) state.catalogPage = totalPages;
  const start = (state.catalogPage - 1) * CATALOG_PER_PAGE;
  const paged = filtered.slice(start, start + CATALOG_PER_PAGE);

  list.innerHTML = `
  <div class="overflow-x-auto rounded-2xl border-[1.5px] border-[#444]">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-[#FFDDAF]/30 border-b-[1.5px] border-[#444]">
          <th class="text-left py-3 px-4 font-bold text-[#444] text-xs uppercase tracking-wider">Buku</th>
          <th class="text-left py-3 px-4 font-bold text-[#444] text-xs uppercase tracking-wider hidden sm:table-cell">ISBN</th>
          <th class="text-left py-3 px-4 font-bold text-[#444] text-xs uppercase tracking-wider hidden md:table-cell">Kategori</th>
          <th class="text-center py-3 px-4 font-bold text-[#444] text-xs uppercase tracking-wider">Status</th>
          <th class="text-center py-3 px-4 font-bold text-[#444] text-xs uppercase tracking-wider">Bisa Dipinjam</th>
          <th class="text-center py-3 px-4 font-bold text-[#444] text-xs uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        ${paged.map(b => `
        <tr class="hover:bg-gray-50/50 transition-colors" data-book-id="${b.id}">
          <td class="py-3 px-4">
            <div class="flex items-center gap-3">
              ${b.cover_url
                ? `<img src="${b.cover_url}" alt="${b.judul}" class="w-10 h-14 rounded-lg border-[1.5px] border-[#444] object-cover flex-shrink-0" />`
                : `<div class="w-10 h-14 rounded-lg bg-gradient-to-br from-[#C7E7FF] to-[#D4F6FF] border-[1.5px] border-[#444] flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-black text-[#444]/50">${getInitial(b.judul)}</span>
              </div>`}
              <div class="min-w-0">
                <p class="font-bold text-[13px] text-[#444] truncate">${b.judul}</p>
                <p class="text-xs text-gray-400 truncate">${b.penulis} · ${b.tahun_terbit}</p>
              </div>
            </div>
          </td>
          <td class="py-3 px-4 text-xs text-gray-400 font-mono hidden sm:table-cell">${b.isbn || ''}</td>
          <td class="py-3 px-4 hidden md:table-cell"><span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#D4F6FF] text-[#444] border-[1.5px] border-[#444]/20">${b.kategori}</span></td>
          <td class="py-3 px-4 text-center">
            ${b.status === 'dipinjam' 
              ? `<span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold border-[1.5px] bg-amber-50 text-amber-600 border-amber-200">Dipinjam</span>`
              : (b.is_available 
                  ? `<span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold border-[1.5px] bg-green-50 text-green-600 border-green-200">Tersedia</span>`
                  : `<span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold border-[1.5px] bg-gray-50 text-gray-500 border-gray-200">Tidak Tersedia</span>`
                )
            }
          </td>
          <td class="py-3 px-4 text-center">
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" class="sr-only peer" data-toggle-avail="${b.id}" ${b.is_available?'checked':''} ${b.status==='dipinjam'?'disabled':''}>
              <div class="w-10 h-[22px] bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-[18px] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-[18px] after:w-[18px] after:transition-all peer-checked:bg-green-500 ${b.status==='dipinjam'?'opacity-50':''}"></div>
            </label>
          </td>
          <td class="py-3 px-4 text-center">
            <button data-delete-book="${b.id}" class="text-gray-300 hover:text-red-500 transition-colors p-1" title="Hapus buku" ${b.status==='dipinjam'?'disabled':''}>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
          </td>
        </tr>`).join('')}
      </tbody>
    </table>
  </div>

  <div class="flex items-center justify-between mt-4">
    <p class="text-xs text-gray-400">Halaman ${state.catalogPage} dari ${totalPages}</p>
    <div class="flex items-center gap-2">
      <button data-catalog-page="prev" ${state.catalogPage <= 1 ? 'disabled' : ''}
              class="px-3 py-1.5 text-xs font-medium rounded-lg border-[1.5px] border-[#444] transition-colors
                     ${state.catalogPage <= 1 ? 'opacity-30 cursor-not-allowed bg-gray-50' : 'bg-white hover:bg-[#FFDDAF] cursor-pointer'}">
        Prev
      </button>
      ${Array.from({length: totalPages}, (_, i) => i + 1).map(p => `
        <button data-catalog-page="${p}"
                class="w-8 h-8 rounded-lg text-xs font-medium border-[1.5px] transition-colors cursor-pointer
                       ${p === state.catalogPage ? 'bg-[#FFDDAF] border-[#444] text-[#444] font-bold' : 'bg-white border-gray-200 text-gray-400 hover:border-gray-400'}">
          ${p}
        </button>
      `).join('')}
      <button data-catalog-page="next" ${state.catalogPage >= totalPages ? 'disabled' : ''}
              class="px-3 py-1.5 text-xs font-medium rounded-lg border-[1.5px] border-[#444] transition-colors
                     ${state.catalogPage >= totalPages ? 'opacity-30 cursor-not-allowed bg-gray-50' : 'bg-white hover:bg-[#FFDDAF] cursor-pointer'}">
        Next
      </button>
    </div>
  </div>`;

  list.querySelectorAll('[data-toggle-avail]').forEach(cb => {
    cb.addEventListener('change', async () => {
      const id = parseInt(cb.dataset.toggleAvail);
      const book = state.catalogData.find(b=>b.id===id);
      if (!book) return;

      const prev = book.is_available;
      book.is_available = cb.checked;

      try {
        const updated = await apiCall('PATCH', `/personal-books/${id}`, { is_available: cb.checked });
        Object.assign(book, updated);
        toast(cb.checked ? `"${book.judul}" dibuka untuk peminjaman` : `"${book.judul}" ditutup dari peminjaman`);
        renderCatalog();
      } catch (err) {
        book.is_available = prev; 
        cb.checked = prev;
        toast('Gagal mengubah status', 'error');
      }
    });
  });

  list.querySelectorAll('[data-delete-book]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = parseInt(btn.dataset.deleteBook);
      const book = state.catalogData.find(b=>b.id===id);
      if (!book) return;

      if (book.status == 'dipinjam') {
        toast('Buku sedang dipinjam, tidak bisa dihapus', 'error');
        return;
      }

      if (!confirm(`Hapus "${book.judul}" dari koleksi?`)) return;

      try {
        await apiCall('DELETE', `/personal-books/${id}`);
        state.catalogData = state.catalogData.filter(b => b.id !== id);
        renderCatalog();
        renderSidebarProfile();
        toast('Buku berhasil dihapus');
      } catch (err) {
        toast(err.message, 'error');
      }
    });
  });

  list.querySelectorAll('[data-catalog-page]').forEach(btn => {
    btn.addEventListener('click', () => {
      const val = btn.dataset.catalogPage;
      if (val === 'prev' && state.catalogPage > 1) state.catalogPage--;
      else if (val === 'next' && state.catalogPage < totalPages) state.catalogPage++;
      else if (val !== 'prev' && val !== 'next') state.catalogPage = Number(val);
      renderCatalog();
    });
  });
}

export function openAddBookModal() {
  $('#add-book-modal').classList.remove('hidden');
  $('#add-book-modal').classList.add('flex');
  document.body.style.overflow = 'hidden';
  setTimeout(() => $('#book-search-input')?.focus(), 100);
}

export function closeAddBookModal() {
  $('#add-book-modal').classList.add('hidden');
  $('#add-book-modal').classList.remove('flex');
  document.body.style.overflow = '';
  $('#add-book-form')?.reset();
  $('#book-search-input').value = '';
  $('#add-book-cover-url').value = '';
  hideSearchResults();
  hideBookPreview();
}

export async function handleAddBook(e) {
  e.preventDefault();
  const fd = new FormData(e.target);
  const data = {
    judul: fd.get('judul')?.trim(),
    penulis: fd.get('penulis')?.trim(),
    isbn: fd.get('isbn')?.trim() || undefined,
    tahun_terbit: parseInt(fd.get('tahun_terbit')) || undefined,
    kategori: fd.get('kategori') || 'Fiksi',
    cover_url: fd.get('foto_sampul') || undefined,
    jumlah_halaman: parseInt(fd.get('halaman')) || undefined,
  };

  if (!data.judul || !data.penulis) {
    toast('Judul dan penulis wajib diisi', 'error');
    return;
  }

  try {
    const book = await apiCall('POST', '/personal-books', data);
    state.catalogData.unshift(book);
    closeAddBookModal();
    renderCatalog();
    renderSidebarProfile();
    toast(`"${book.judul}" berhasil ditambahkan!`);
  } catch (err) {
    toast(err.message, 'error');
  }
}
