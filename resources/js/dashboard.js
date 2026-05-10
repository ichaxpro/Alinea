// ══════════════════════════════════════
// ALINEA — DASHBOARD JS
// ══════════════════════════════════════

// ── MOCK DATA ──
const CURRENT_USER = {
  id: 1, nama: 'Budi Ashcroft', username: 'isoba__', email: 'budi@alinea.id',
  kota: 'Malang', no_telp: '08123456789', bio: 'Pecinta buku fiksi dan thriller.',
  preferred_genres: ['Fiksi', 'Thriller', 'Misteri'],
  foto_profil: null, member_since: '2025-12-01',
};

const ALL_GENRES = ['Fiksi','Non-Fiksi','Thriller','Misteri','Romansa','Sci-Fi','Fantasi','Horror','Biografi','Sejarah','Pengembangan Diri','Bisnis','Puisi','Komik'];

const MY_CATALOG = [
  { id:1, judul:'Pulang', penulis:'Tere Liye', isbn:'978-602-0851-00-7', tahun_terbit:2015, kategori:'Fiksi', foto_sampul:null, is_available:true, status:'tersedia' },
  { id:2, judul:'Bumi', penulis:'Tere Liye', isbn:'978-602-0851-01-4', tahun_terbit:2014, kategori:'Fantasi', foto_sampul:null, is_available:true, status:'tersedia' },
  { id:3, judul:'Atomic Habits', penulis:'James Clear', isbn:'978-0-7352-1129-2', tahun_terbit:2018, kategori:'Pengembangan Diri', foto_sampul:null, is_available:false, status:'dipinjam' },
  { id:4, judul:'Sapiens', penulis:'Yuval Noah Harari', isbn:'978-0-06-231609-7', tahun_terbit:2011, kategori:'Non-Fiksi', foto_sampul:null, is_available:true, status:'tersedia' },
];

const TRANSACTIONS = [
  { id:1, buku:{ judul:'Harry Potter', penulis:'J.K. Rowling', foto_sampul:null }, pemilik:{ nama:'Dina Rahmawati', kota:'Surabaya' }, tanggal_pinjam:'2026-04-20', tanggal_kembali_rencana:'2026-05-04', tanggal_pengembalian_aktual:null, status_transaksi:'pending', titik_temu_pinjam:'Toko Buku Gramedia Surabaya' },
  { id:2, buku:{ judul:'The Midnight Library', penulis:'Matt Haig', foto_sampul:null }, pemilik:{ nama:'Ahmad Fauzan', kota:'Bandung' }, tanggal_pinjam:'2026-04-15', tanggal_kembali_rencana:'2026-04-29', tanggal_pengembalian_aktual:null, status_transaksi:'on_loan', titik_temu_pinjam:'Kafe Kopi Kenangan Bandung' },
  { id:3, buku:{ judul:'Laskar Pelangi', penulis:'Andrea Hirata', foto_sampul:null }, pemilik:{ nama:'Reza Mahendra', kota:'Jakarta' }, tanggal_pinjam:'2026-03-01', tanggal_kembali_rencana:'2026-03-15', tanggal_pengembalian_aktual:null, status_transaksi:'on_loan', titik_temu_pinjam:'Perpustakaan UI Depok' },
  { id:4, buku:{ judul:'Filosofi Teras', penulis:'Henry Manampiring', foto_sampul:null }, pemilik:{ nama:'Siti Rahmawati', kota:'Yogyakarta' }, tanggal_pinjam:'2026-02-10', tanggal_kembali_rencana:'2026-02-24', tanggal_pengembalian_aktual:'2026-02-22', status_transaksi:'returned', titik_temu_pinjam:'Malioboro Mall' },
  { id:5, buku:{ judul:'Negeri 5 Menara', penulis:'A. Fuadi', foto_sampul:null }, pemilik:{ nama:'Maya Putri', kota:'Malang' }, tanggal_pinjam:'2026-01-05', tanggal_kembali_rencana:'2026-01-19', tanggal_pengembalian_aktual:'2026-01-20', status_transaksi:'returned', titik_temu_pinjam:'Alun-Alun Malang' },
];

// ── STATE ──
let activeTab = 'personal';
let catalogData = [...MY_CATALOG];
let txFilter = 'all';
let catalogSearch = '';
let nextCatalogId = MY_CATALOG.length + 1;
let selectedGenres = [...CURRENT_USER.preferred_genres];

// ── HELPERS ──
const $ = s => document.querySelector(s);
const $$ = s => document.querySelectorAll(s);
const fmt = d => { if(!d) return '—'; const dt=new Date(d); return dt.toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'}); };

function isOverdue(tx) {
  if (tx.status_transaksi !== 'on_loan') return false;
  return new Date(tx.tanggal_kembali_rencana) < new Date();
}

function getTxStatus(tx) {
  if (tx.status_transaksi === 'pending') return 'pending';
  if (tx.status_transaksi === 'returned') return 'returned';
  if (tx.status_transaksi === 'on_loan' && isOverdue(tx)) return 'overdue';
  if (tx.status_transaksi === 'on_loan') return 'on_loan';
  return tx.status_transaksi;
}

function statusLabel(s) {
  const m = { pending:'Pengajuan', on_loan:'Sedang Dipinjam', overdue:'Overdue', returned:'Dikembalikan', accepted:'Diterima', rejected:'Ditolak', cancelled:'Dibatalkan' };
  return m[s]||s;
}
function statusColor(s) {
  const m = { pending:'bg-yellow-100 text-yellow-700 border-yellow-300', on_loan:'bg-blue-100 text-blue-700 border-blue-300', overdue:'bg-red-100 text-red-700 border-red-300', returned:'bg-green-100 text-green-700 border-green-300', accepted:'bg-emerald-100 text-emerald-700 border-emerald-300', rejected:'bg-gray-100 text-gray-500 border-gray-300', cancelled:'bg-gray-100 text-gray-400 border-gray-300' };
  return m[s]||'bg-gray-100 text-gray-500 border-gray-300';
}

function toast(msg, type='success') {
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

function getInitial(name) { return name ? name.charAt(0).toUpperCase() : '?'; }

// ── TAB NAVIGATION ──
function switchTab(tab) {
  activeTab = tab;
  $$('[data-tab-btn]').forEach(b => {
    const isActive = b.dataset.tabBtn === tab;
    b.classList.toggle('bg-[#FFDDAF]', isActive);
    b.classList.toggle('text-[#444]', isActive);
    b.classList.toggle('font-bold', isActive);
    b.classList.toggle('text-gray-400', !isActive);
    b.classList.toggle('font-medium', !isActive);
  });
  $$('[data-tab-panel]').forEach(p => {
    p.classList.toggle('hidden', p.dataset.tabPanel !== tab);
  });
  if (tab === 'transaksi') renderTransactions();
  if (tab === 'katalog') renderCatalog();
  if (tab === 'personal') renderGenrePicker();
}

// ── GENRE PICKER ──
function renderGenrePicker() {
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

// ── TRANSACTIONS ──
function renderTransactions() {
  const list = $('#tx-list');
  if (!list) return;

  const filtered = TRANSACTIONS.filter(tx => {
    const s = getTxStatus(tx);
    if (txFilter === 'all') return true;
    return s === txFilter;
  });

  // Update counts
  const counts = { all:TRANSACTIONS.length, pending:0, on_loan:0, overdue:0, returned:0 };
  TRANSACTIONS.forEach(tx => { const s=getTxStatus(tx); counts[s]=(counts[s]||0)+1; });
  $$('[data-tx-count]').forEach(el => {
    const k = el.dataset.txCount;
    el.textContent = counts[k]||0;
  });

  if (filtered.length === 0) {
    list.innerHTML = `<div class="text-center py-16"><div class="text-4xl mb-3">📭</div><p class="text-sm text-gray-400 font-medium">Tidak ada transaksi di kategori ini.</p></div>`;
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
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border-[1.5px] flex-shrink-0 ${statusColor(s)}">${statusLabel(s)}</span>
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
          <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Titik temu: ${tx.titik_temu_pinjam}</p>
        </div>
      </div>
    </div>`;
  }).join('');
}

// ── CATALOG ──
function renderCatalog() {
  const list = $('#catalog-list');
  if (!list) return;

  const filtered = catalogData.filter(b =>
    !catalogSearch || b.judul.toLowerCase().includes(catalogSearch.toLowerCase()) || b.penulis.toLowerCase().includes(catalogSearch.toLowerCase())
  );

  $('#catalog-count').textContent = `${filtered.length} buku`;

  if (filtered.length === 0) {
    list.innerHTML = `<div class="text-center py-16"><div class="text-4xl mb-3">📚</div><p class="text-sm text-gray-400 font-medium">${catalogSearch ? 'Tidak ada buku ditemukan.' : 'Koleksimu masih kosong. Tambahkan buku pertamamu!'}</p></div>`;
    return;
  }

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
        ${filtered.map(b => `
        <tr class="hover:bg-gray-50/50 transition-colors" data-book-id="${b.id}">
          <td class="py-3 px-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-14 rounded-lg bg-gradient-to-br from-[#C7E7FF] to-[#D4F6FF] border-[1.5px] border-[#444] flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-black text-[#444]/50">${getInitial(b.judul)}</span>
              </div>
              <div class="min-w-0">
                <p class="font-bold text-[13px] text-[#444] truncate">${b.judul}</p>
                <p class="text-xs text-gray-400 truncate">${b.penulis} · ${b.tahun_terbit}</p>
              </div>
            </div>
          </td>
          <td class="py-3 px-4 text-xs text-gray-400 font-mono hidden sm:table-cell">${b.isbn}</td>
          <td class="py-3 px-4 hidden md:table-cell"><span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#D4F6FF] text-[#444] border-[1.5px] border-[#444]/20">${b.kategori}</span></td>
          <td class="py-3 px-4 text-center"><span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold border-[1.5px] ${b.status==='tersedia' ? 'bg-green-50 text-green-600 border-green-200' : 'bg-amber-50 text-amber-600 border-amber-200'}">${b.status==='tersedia'?'Tersedia':'Dipinjam'}</span></td>
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
  </div>`;

  // Toggle handlers
  list.querySelectorAll('[data-toggle-avail]').forEach(cb => {
    cb.addEventListener('change', () => {
      const id = parseInt(cb.dataset.toggleAvail);
      const book = catalogData.find(b=>b.id===id);
      if (book) { book.is_available = cb.checked; toast(cb.checked?`"${book.judul}" dibuka untuk peminjaman`:`"${book.judul}" ditutup dari peminjaman`); }
    });
  });

  // Delete handlers
  list.querySelectorAll('[data-delete-book]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = parseInt(btn.dataset.deleteBook);
      const book = catalogData.find(b=>b.id===id);
      if (book && book.status==='dipinjam') { toast('Buku sedang dipinjam, tidak bisa dihapus','error'); return; }
      if (confirm(`Hapus "${book?.judul}" dari koleksi?`)) {
        catalogData = catalogData.filter(b=>b.id!==id);
        renderCatalog();
        toast('Buku berhasil dihapus');
      }
    });
  });
}

// ── ADD BOOK MODAL ──
function openAddBookModal() {
  $('#add-book-modal').classList.remove('hidden');
  $('#add-book-modal').classList.add('flex');
  document.body.style.overflow = 'hidden';
  setTimeout(() => $('#add-book-judul')?.focus(), 100);
}
function closeAddBookModal() {
  $('#add-book-modal').classList.add('hidden');
  $('#add-book-modal').classList.remove('flex');
  document.body.style.overflow = '';
  $('#add-book-form')?.reset();
}

function handleAddBook(e) {
  e.preventDefault();
  const fd = new FormData(e.target);
  const judul = fd.get('judul')?.trim();
  const penulis = fd.get('penulis')?.trim();
  const isbn = fd.get('isbn')?.trim();
  const tahun = parseInt(fd.get('tahun_terbit'));
  const kategori = fd.get('kategori');

  if (!judul||!penulis) { toast('Judul dan penulis wajib diisi','error'); return; }

  catalogData.push({ id: nextCatalogId++, judul, penulis, isbn: isbn||'—', tahun_terbit: tahun||new Date().getFullYear(), kategori: kategori||'Fiksi', foto_sampul:null, is_available:true, status:'tersedia' });

  closeAddBookModal();
  renderCatalog();
  toast(`"${judul}" berhasil ditambahkan!`);
}

// ── PASSWORD ──
function handleChangePassword(e) {
  e.preventDefault();
  const cur = $('#pw-current')?.value;
  const nw = $('#pw-new')?.value;
  const conf = $('#pw-confirm')?.value;
  if (!cur||!nw||!conf) { toast('Semua field wajib diisi','error'); return; }
  if (nw.length < 8) { toast('Password baru minimal 8 karakter','error'); return; }
  if (nw !== conf) { toast('Konfirmasi password tidak cocok','error'); return; }
  toast('Password berhasil diubah!');
  e.target.reset();
}

// ── PERSONAL INFO ──
function handleSaveProfile(e) {
  e.preventDefault();
  const nama = $('#prof-nama')?.value?.trim();
  const kota = $('#prof-kota')?.value?.trim();
  if (!nama) { toast('Nama tidak boleh kosong','error'); return; }
  CURRENT_USER.nama = nama;
  CURRENT_USER.kota = kota;
  CURRENT_USER.preferred_genres = [...selectedGenres];
  toast('Profil berhasil disimpan!');
  renderSidebarProfile();
}

function renderSidebarProfile() {
  const el = $('#sidebar-name');
  if (el) el.textContent = CURRENT_USER.nama;
  const el2 = $('#sidebar-location');
  if (el2) el2.textContent = CURRENT_USER.kota || '—';
  const el3 = $('#profile-initial');
  if (el3) el3.textContent = getInitial(CURRENT_USER.nama);
}

// ── INIT ──
document.addEventListener('DOMContentLoaded', () => {
  // Tab buttons
  $$('[data-tab-btn]').forEach(btn => {
    btn.addEventListener('click', () => switchTab(btn.dataset.tabBtn));
  });

  // Transaction filter
  $$('[data-tx-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
      txFilter = btn.dataset.txFilter;
      $$('[data-tx-filter]').forEach(b => {
        const isActive = b.dataset.txFilter === txFilter;
        b.classList.toggle('bg-[#FFDDAF]', isActive);
        b.classList.toggle('border-[#444]', isActive);
        b.classList.toggle('text-[#444]', isActive);
        b.classList.toggle('font-bold', isActive);
        b.classList.toggle('bg-white', !isActive);
        b.classList.toggle('border-gray-200', !isActive);
        b.classList.toggle('text-gray-400', !isActive);
        b.classList.toggle('font-medium', !isActive);
      });
      renderTransactions();
    });
  });

  // Catalog search
  const catSearch = $('#catalog-search');
  if (catSearch) {
    catSearch.addEventListener('input', (e) => { catalogSearch = e.target.value; renderCatalog(); });
  }

  // Add book
  $('#btn-add-book')?.addEventListener('click', openAddBookModal);
  $('#close-add-book')?.addEventListener('click', closeAddBookModal);
  $('#add-book-modal')?.addEventListener('click', (e) => { if(e.target.id==='add-book-modal') closeAddBookModal(); });
  $('#add-book-form')?.addEventListener('submit', handleAddBook);

  // Forms
  $('#security-form')?.addEventListener('submit', handleChangePassword);
  $('#profile-form')?.addEventListener('submit', handleSaveProfile);

  // Password visibility toggles
  $$('[data-toggle-pw]').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.togglePw);
      if (input) {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.querySelector('.eye-open')?.classList.toggle('hidden', !isPassword);
        btn.querySelector('.eye-closed')?.classList.toggle('hidden', isPassword);
      }
    });
  });

  // Mobile sidebar toggle
  $('#mobile-sidebar-toggle')?.addEventListener('click', () => {
    $('#mobile-sidebar-menu')?.classList.toggle('hidden');
  });

  // Init
  switchTab('personal');
  renderSidebarProfile();
});
