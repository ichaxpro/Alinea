import { apiCall } from './api.js';
import { $, toast, getInitial, escapeHtml } from './utils.js';
import { state } from './state.js';

const ALL_GENRES = ['Fiksi','Non-Fiksi','Thriller','Misteri','Romansa','Sci-Fi','Fantasi','Horror','Biografi','Sejarah','Pengembangan Diri','Bisnis','Puisi','Komik'];
export let selectedGenres = [];

export function initProfile() {
    if (window.CURRENT_USER && window.CURRENT_USER.preferred_genres) {
        selectedGenres = [...window.CURRENT_USER.preferred_genres];
    }
}

export function renderGenrePicker() {
  const c = $('#genre-picker');
  if (!c) return;
  c.innerHTML = ALL_GENRES.map(g => {
    const sel = selectedGenres.includes(g);
    return `<button type="button" data-genre="${g}" class="genre-chip px-3 py-1.5 rounded-full text-xs font-medium border-[1.5px] transition-all duration-200 cursor-pointer ${sel ? 'bg-[#FFDDAF] border-[#444] text-[#444]' : 'bg-white border-gray-200 text-gray-400 hover:border-gray-400'}">${g}</button>`;
  }).join('');
  c.querySelectorAll('.genre-chip').forEach(btn => {
    btn.addEventListener('click', () => {
      const g = btn.dataset.genre;
      if (selectedGenres.includes(g)) selectedGenres = selectedGenres.filter(x=>x!==g);
      else if (selectedGenres.length < 5) selectedGenres.push(g);
      else { toast('Maksimal 5 genre','info'); return; }
      renderGenrePicker();
    });
  });
}

export function handleSaveProfile(e) {
  e.preventDefault();
  const nama = $('#prof-nama')?.value?.trim();
  const kota = $('#prof-kota')?.value?.trim();
  if (!nama) { toast('Nama tidak boleh kosong','error'); return; }
  window.CURRENT_USER.nama = nama;
  window.CURRENT_USER.kota = kota;
  window.CURRENT_USER.preferred_genres = [...selectedGenres];
  toast('Profil berhasil disimpan!');
  renderSidebarProfile();
}

export async function handleChangePassword(e) {
  e.preventDefault();
  const cur = $('#pw-current')?.value;
  const nw = $('#pw-new')?.value;
  const conf = $('#pw-confirm')?.value;
  if (!cur||!nw||!conf) { toast('Semua field wajib diisi','error'); return; }
  if (nw.length < 8) { toast('Password baru minimal 8 karakter','error'); return; }
  if (nw !== conf) { toast('Konfirmasi password tidak cocok','error'); return; }

  try {
    const res = await apiCall('POST', '/change-password', {
      current_password: cur,
      new_password: nw,
      new_password_confirmation: conf,
    });
    toast(res.message || 'Kata sandi berhasil diubah!');
    e.target.reset();
  } catch (err) {
    toast(err.message || 'Gagal mengubah kata sandi', 'error');
  }
}

export async function loadBookmarks() {
  const list = document.getElementById('bookmarks-list');
  const empty = document.getElementById('bookmarks-empty');
  if (!list) return;

  try {
    const res = await fetch('/api/bookmarks', {headers: {Accept: 'application/json'}});
    const data = await res.json();
    const books = data.data ?? [];

    list.innerHTML = '';

    if (books.length === 0) {
      list.classList.add('hidden');
      empty?.classList.remove('hidden');
      return;
    }

    list.classList.remove('hidden');
    empty?.classList.add('hidden');

    books.forEach(book => {
      const url = book.identifier_type === 'db' ? `/detail-buku/${book.book_identifier}` : `/detail-buku/${book.book_identifier}`;

      const card = document.createElement('a');
      card.href = url;
      card.className = 'group block rounded-2xl overflow-hidden border-[1.5px] border-gray-200 hover:border-[#444] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md';
      card.innerHTML = `
          <div class="aspect-[2/3] overflow-hidden bg-gradient-to-br from-[#D4F6FF] to-[#FFDDAF]">
              ${book.foto_sampul
                  ? `<img src="${escapeHtml(book.foto_sampul)}" alt="${escapeHtml(book.judul)}" class="w-full h-full object-cover" />`
                  : `<div class="w-full h-full flex items-center justify-center">
                         <span class="text-3xl font-black text-[#444]/30">${escapeHtml(book.judul.charAt(0))}</span>
                     </div>`
              }
          </div>
          <div class="p-3">
              <p class="font-bold text-[13px] text-[#444] leading-tight line-clamp-2 mb-0.5">${escapeHtml(book.judul)}</p>
              <p class="text-[11px] text-gray-400 truncate">${escapeHtml(book.penulis ?? '')}</p>
              ${book.kategori ? `<span class="inline-block mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FFDDAF]/40 text-[#444]">${escapeHtml(book.kategori)}</span>` : ''}
          </div>
      `;
      list.appendChild(card);
    });
  } catch (e) {
    console.error('Gagal load bookmarks: ', e);
  }
}

export function updateSidebarStats() {
  const statKoleksi = $('#stat-koleksi');
  if (statKoleksi) statKoleksi.textContent = state.catalogData.length;
  
  const statTx = $('#stat-transaksi');
  if (statTx) statTx.textContent = state.transactions.length;
  
  const statPengajuan = $('#stat-pengajuan');
  if (statPengajuan) {
      statPengajuan.textContent = state.pengajuanPinjam.filter(x => x.status === 'pending').length;
  }
}

export function renderSidebarProfile() {
  const CURRENT_USER = window.CURRENT_USER || {};
  const el = $('#sidebar-name');
  if (el) el.textContent = CURRENT_USER.nama;
  const el2 = $('#sidebar-location');
  if (el2) el2.textContent = CURRENT_USER.kota || '—';
  const el3 = $('#profile-initial');
  if (el3) el3.textContent = getInitial(CURRENT_USER.nama);
  
  const el4 = $('#sidebar-username');
  if (el4) el4.textContent = CURRENT_USER.username ? `@${CURRENT_USER.username}` : '';
  
  updateSidebarStats();
  
  const avatarImg = document.getElementById('profile-avatar-img');
  const avatarInitial = document.getElementById('profile-initial');
  if (CURRENT_USER.foto_profil) {
    avatarImg.src = CURRENT_USER.foto_profil;
    avatarImg.classList.remove('hidden');
    avatarInitial.classList.add('hidden');
  } else {
    avatarImg.classList.add('hidden');
    avatarInitial.classList.remove('hidden');
    avatarInitial.textContent = getInitial(CURRENT_USER.nama);
  }
}

export function populateProfileForm() {
  const CURRENT_USER = window.CURRENT_USER || {};
  const nama = $('#prof-nama');
  if (nama) nama.value = CURRENT_USER.nama || '';
  const email = $('#prof-email');
  if (email) email.value = CURRENT_USER.email || '';
  const kota = $('#prof-kota');
  if (kota) kota.value = CURRENT_USER.kota || '';
  const telp = $('#prof-telp');
  if (telp) telp.value = CURRENT_USER.no_telp || '';
}
