export const $ = s => document.querySelector(s);
export const $$ = s => document.querySelectorAll(s);

export const fmt = d => {
    if(!d) return '—';
    const dt = new Date(d);
    return dt.toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'});
};

export function statusLabel(s) {
  const m = { pending:'Pengajuan', on_loan:'Sedang Dipinjam', overdue:'Terlambat', returned:'Dikembalikan', accepted:'Diterima', rejected:'Ditolak', cancelled:'Dibatalkan', pending_return:'Proses Pengembalian' };
  return m[s]||s;
}

export function statusColor(s) {
  const m = { pending:'bg-yellow-100 text-yellow-700 border-yellow-300', on_loan:'bg-blue-100 text-blue-700 border-blue-300', overdue:'bg-red-100 text-red-700 border-red-300', returned:'bg-green-100 text-green-700 border-green-300', accepted:'bg-emerald-100 text-emerald-700 border-emerald-300', rejected:'bg-gray-100 text-gray-500 border-gray-300', cancelled:'bg-gray-100 text-gray-400 border-gray-300', pending_return:'bg-orange-100 text-orange-700 border-orange-300' };
  return m[s]||'bg-gray-100 text-gray-500 border-gray-300';
}

export function toast(msg, type='success') {
  const c = $('#toastContainer');
  if (!c) return;
  const t = document.createElement('div');
  const colors = { success:'bg-green-600', error:'bg-red-600', info:'bg-blue-600' };
  t.className = `${colors[type]||colors.info} text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg transform translate-y-3 opacity-0 transition-all duration-300`;
  t.textContent = msg;
  c.appendChild(t);
  requestAnimationFrame(() => { t.classList.remove('translate-y-3','opacity-0'); });
  setTimeout(() => { t.classList.add('translate-y-3','opacity-0'); setTimeout(()=>t.remove(),300); }, 2500);
}

export function getInitial(name) {
    return name ? name.charAt(0).toUpperCase() : '?';
}

export function escapeHtml(str) {
  if (!str) {
    return '';
  }
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
