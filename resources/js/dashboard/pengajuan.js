import { apiCall } from './api.js';
import { $, fmt, statusLabel, statusColor, getInitial, toast } from './utils.js';
import { state } from './state.js';
import { updateSidebarStats } from './profile.js';

export async function loadPengajuan() {
  try {
    const data = await apiCall('GET', '/transactions/incoming');
    state.pengajuanPinjam = data.map(tx => ({
        id: tx.id,
        buku: { judul: tx.book.judul, penulis: tx.book.penulis },
        peminjam: { id: tx.borrower.id, nama: tx.borrower.name, kota: tx.borrower.kota },
        tanggal_pinjam: tx.tanggal_pinjam_rencana,
        tanggal_kembali_rencana: tx.tanggal_kembali_rencana,
        status: tx.status,
        titik_temu: tx.titik_temu
    }));
    renderPengajuan();
    updateSidebarStats();
  } catch(err) {
    toast('Gagal memuat pengajuan pinjam', 'error');
  }
}

export function renderPengajuan() {
  const list = $('#pengajuan-list');
  if (!list) return;

  const statEl = $('#stat-pengajuan');
  if (statEl) statEl.textContent = state.pengajuanPinjam.filter(x => x.status === 'pending').length;

  const counts = {
      all: state.pengajuanPinjam.length,
      incoming: state.pengajuanPinjam.filter(x => x.status === 'pending').length,
      ongoing: state.pengajuanPinjam.filter(x => ['accepted', 'on_loan', 'pending_return'].includes(x.status)).length,
      history: state.pengajuanPinjam.filter(x => ['returned', 'rejected'].includes(x.status)).length
  };

  document.querySelectorAll('[data-pengajuan-filter]').forEach(btn => {
      const f = btn.dataset.pengajuanFilter;
      const countEl = btn.querySelector('span');
      if (countEl) countEl.textContent = counts[f];

      if (f === state.pengajuanFilter) {
          btn.className = "px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-[#FFDDAF] text-[#444] border-[1.5px] border-[#444] shadow-sm cursor-pointer";
      } else {
          btn.className = "px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-gray-500 hover:bg-gray-100 border-[1.5px] border-transparent cursor-pointer";
      }

      if (!btn.dataset.hasListener) {
          btn.addEventListener('click', () => {
              state.pengajuanFilter = f;
              renderPengajuan();
          });
          btn.dataset.hasListener = "true";
      }
  });

  const filtered = state.pengajuanPinjam.filter(p => {
      if (state.pengajuanFilter === 'incoming') return p.status === 'pending';
      if (state.pengajuanFilter === 'ongoing') return ['accepted', 'on_loan', 'pending_return'].includes(p.status);
      if (state.pengajuanFilter === 'history') return ['returned', 'rejected'].includes(p.status);
      return true;
  });

  if (filtered.length === 0) {
    list.innerHTML = `<div class="text-center py-16"><div class="text-4xl mb-3"></div><p class="text-sm text-gray-400 font-medium">Tidak ada pengajuan ditemukan di kategori ini.</p></div>`;
    return;
  }

  list.innerHTML = filtered.map(p => {
    let actions = '';
    if (p.status === 'pending') {
      actions = `
        <div class="flex items-center gap-2 mt-3 sm:mt-0 sm:ml-auto">
          <button data-action="pengajuan-tolak" data-id="${p.id}" class="px-4 py-2 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 border-[1.5px] border-red-200 rounded-lg transition-colors cursor-pointer">Tolak</button>
          <button data-action="pengajuan-terima" data-id="${p.id}" class="px-4 py-2 text-xs font-bold text-[#444] bg-[#FFDDAF] hover:bg-[#ffcf90] border-[1.5px] border-[#444] rounded-lg transition-colors cursor-pointer">Terima</button>
          <a href="/chat?user_id=${p.peminjam.id}" class="px-4 py-2 text-xs font-bold text-[#444] bg-[#C7E7FF] hover:bg-[#b0dcff] border-[1.5px] border-[#444] rounded-lg transition-colors flex items-center gap-1.5"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Chat</a>
        </div>
      `;
    } else if (p.status === 'pending_return') {
      actions = `
        <div class="flex items-center gap-2 mt-3 sm:mt-0 sm:ml-auto">
          <button data-action="accept-return" data-id="${p.id}" class="px-4 py-2 text-xs font-bold text-[#444] bg-[#FFDDAF] hover:bg-[#ffcf90] border-[1.5px] border-[#444] rounded-lg transition-colors cursor-pointer">Terima Pengembalian</button>
          <a href="/chat?user_id=${p.peminjam.id}" class="px-4 py-2 text-xs font-bold text-[#444] bg-[#C7E7FF] hover:bg-[#b0dcff] border-[1.5px] border-[#444] rounded-lg transition-colors flex items-center gap-1.5"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Chat</a>
        </div>
      `;
    } else {
      actions = `
        <div class="flex items-center gap-2 mt-3 sm:mt-0 sm:ml-auto">
          ${p.status === 'accepted' ? `<a href="/chat?user_id=${p.peminjam.id}" class="px-4 py-2 text-xs font-bold text-[#444] bg-[#C7E7FF] hover:bg-[#b0dcff] border-[1.5px] border-[#444] rounded-lg transition-colors flex items-center gap-1.5"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Chat</a>` : ''}
        </div>
      `;
    }

    return `
    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5 hover:shadow-md transition-shadow duration-200">
      <div class="flex flex-col sm:flex-row sm:items-start gap-4">
        <div class="w-14 h-20 rounded-lg bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-[1.5px] border-[#444] flex items-center justify-center flex-shrink-0">
          <span class="text-lg font-black text-[#444]/60">${getInitial(p.buku.judul)}</span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-2 mb-1">
            <h3 class="font-bold text-[15px] text-[#444] truncate">${p.buku.judul}</h3>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border-[1.5px] flex-shrink-0 ${statusColor(p.status)}">${statusLabel(p.status)}</span>
          </div>
          <p class="text-xs text-gray-400 mb-2">${p.buku.penulis}</p>
          <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
            <span class="flex items-center gap-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Peminjam: ${p.peminjam.nama}</span>
            <span class="flex items-center gap-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ${fmt(p.tanggal_pinjam)} — ${fmt(p.tanggal_kembali_rencana)}</span>
          </div>
          <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Titik temu: ${p.titik_temu}</p>
        </div>
        ${actions}
      </div>
    </div>`;
  }).join('');
}

export async function handlePengajuanAction(id, action) {
  const status = action === 'terima' ? 'accepted' : 'rejected';
  try {
      await apiCall('PATCH', `/transactions/${id}/status`, { status });
      
      const p = state.pengajuanPinjam.find(x => x.id === parseInt(id));
      if (p) p.status = status;
      
      if (action === 'terima') {
          toast('Pengajuan diterima! Silakan negosiasi di chat.', 'success');
          state.catalogLoaded = false;
      } else {
          toast('Pengajuan ditolak.', 'info');
      }
      renderPengajuan();
  } catch(err) {
      toast('Gagal mengupdate status', 'error');
  }
}

export async function handleAcceptReturn(id) {
  try {
      await apiCall('PATCH', `/transactions/${id}/accept-return`);
      toast('Buku berhasil dikembalikan!', 'success');
      state.catalogLoaded = false;
      loadPengajuan();
  } catch(err) {
      toast('Gagal memproses pengembalian', 'error');
  }
}
