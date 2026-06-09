import { apiCall } from './api.js';
import { $, $$, fmt, statusLabel, statusColor, getInitial, toast } from './utils.js';
import { state } from './state.js';
import { updateSidebarStats } from './profile.js';

function isOverdue(tx) {
  if (tx.status_transaksi !== 'on_loan' && tx.status_transaksi !== 'accepted') return false;
  return new Date(tx.tanggal_kembali_rencana) < new Date();
}

function getTxStatus(tx) {
  if (tx.status_transaksi === 'rejected') return 'rejected';
  if (tx.status_transaksi === 'pending') return 'pending';
  if (tx.status_transaksi === 'returned') return 'returned';
  if (['on_loan', 'accepted', 'pending_return'].includes(tx.status_transaksi)) {
      if (isOverdue(tx)) return 'overdue';
      return 'on_loan';
  }
  return tx.status_transaksi;
}

export async function loadTransactions() {
  try {
    const data = await apiCall('GET', '/transactions/outgoing');
    state.transactions = data.map(tx => ({
        id: tx.id,
        buku: { judul: tx.book.judul, penulis: tx.book.penulis, foto_sampul: tx.book.cover_url },
        pemilik: { nama: tx.owner.name, kota: tx.owner.kota },
        tanggal_pinjam: tx.tanggal_pinjam_rencana,
        tanggal_kembali_rencana: tx.tanggal_kembali_rencana,
        tanggal_pengembalian_aktual: tx.tanggal_pengembalian_aktual,
        status_transaksi: tx.status,
        titik_temu_pinjam: tx.titik_temu
    }));
    renderTransactions();
    updateSidebarStats();
  } catch(err) {
    toast('Gagal memuat riwayat peminjaman', 'error');
  }
}

export function renderTransactions() {
  const list = $('#tx-list');
  if (!list) return;

  const filtered = state.transactions.filter(tx => {
    const s = getTxStatus(tx);
    if (state.txFilter === 'all') return true;
    return s === state.txFilter;
  });

  const counts = { all: state.transactions.length, pending:0, on_loan:0, overdue:0, returned:0, rejected:0 };
  state.transactions.forEach(tx => { const s = getTxStatus(tx); counts[s] = (counts[s]||0)+1; });
  $$('[data-tx-count]').forEach(el => {
    const k = el.dataset.txCount;
    el.textContent = counts[k]||0;
  });

  if (filtered.length === 0) {
    list.innerHTML = `<div class="text-center py-16"><div class="text-4xl mb-3"></div><p class="text-sm text-gray-400 font-medium">Tidak ada transaksi di kategori ini.</p></div>`;
    return;
  }

  list.innerHTML = filtered.map(tx => {
    const s = getTxStatus(tx);
    const daysLeft = Math.ceil((new Date(tx.tanggal_kembali_rencana)-new Date())/(1000*60*60*24));
    const urgency = s==='on_loan' && daysLeft<=3 && daysLeft>0 ? `<span class="text-xs text-amber-500 font-medium">⚠ ${daysLeft} hari lagi</span>` : '';
    const overdueDay = s==='overdue' ? `<span class="text-xs text-red-500 font-medium">Terlambat ${Math.abs(daysLeft)} hari</span>` : '';

    return `
    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5 hover:shadow-md transition-shadow duration-200">
      <div class="flex items-start gap-4">
        <div class="w-14 h-20 rounded-lg bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-[1.5px] border-[#444] flex items-center justify-center flex-shrink-0">
          <span class="text-lg font-black text-[#444]/60">${getInitial(tx.buku.judul)}</span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-2 mb-1">
            <h3 class="font-bold text-[15px] text-[#444] truncate">${tx.buku.judul}</h3>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border-[1.5px] flex-shrink-0 ${statusColor(tx.status_transaksi)}">${statusLabel(tx.status_transaksi)}</span>
          </div>
          <p class="text-xs text-gray-400 mb-2">${tx.buku.penulis}</p>
          <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
            <span class="flex items-center gap-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> ${tx.pemilik.nama}</span>
            <span class="flex items-center gap-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> ${tx.pemilik.kota}</span>
            <span class="flex items-center gap-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ${fmt(tx.tanggal_pinjam)} — ${fmt(tx.tanggal_kembali_rencana)}</span>
          </div>
          ${tx.tanggal_pengembalian_aktual ? `<p class="text-xs text-green-600 mt-1 font-medium">✓ Dikembalikan ${fmt(tx.tanggal_pengembalian_aktual)}</p>` : ''}
          ${urgency ? `<p class="mt-1">${urgency}</p>` : ''}
          ${overdueDay ? `<p class="mt-1">${overdueDay}</p>` : ''}
          <div class="flex items-center justify-between mt-1.5">
            <p class="text-xs text-gray-400 flex items-center gap-1"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Titik temu: ${tx.titik_temu_pinjam}</p>
            ${(tx.status_transaksi === 'accepted' || tx.status_transaksi === 'on_loan') ? `<button data-action="return-request" data-id="${tx.id}" class="px-4 py-1.5 text-[11px] font-bold text-[#444] bg-[#FFDDAF] hover:bg-[#ffcf90] border-[1.5px] border-[#444] rounded-lg transition-colors cursor-pointer">Kembalikan Buku</button>` : ''}
          </div>
        </div>
      </div>
    </div>`;
  }).join('');
}

export async function handleReturnRequest(id) {
  try {
      await apiCall('PATCH', `/transactions/${id}/request-return`);
      toast('Permintaan pengembalian dikirim!', 'success');
      loadTransactions();
  } catch(err) {
      toast('Gagal meminta pengembalian', 'error');
  }
}
