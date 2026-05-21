// ══════════════════════════════════════
// ALINEA — DASHBOARD JS
// ══════════════════════════════════════

// ── MOCK DATA ──

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
  const m = { pending:'Pengajuan', on_loan:'Sedang Dipinjam', overdue:'Terlambat', returned:'Dikembalikan', accepted:'Diterima', rejected:'Ditolak', cancelled:'Dibatalkan' };
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
              ${b.foto_sampul
                ? `<img src="${b.foto_sampul}" alt="${b.judul}" class="w-10 h-14 rounded-lg border-[1.5px] border-[#444] object-cover flex-shrink-0" />`
                : `<div class="w-10 h-14 rounded-lg bg-gradient-to-br from-[#C7E7FF] to-[#D4F6FF] border-[1.5px] border-[#444] flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-black text-[#444]/50">${getInitial(b.judul)}</span>
              </div>`}
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
        renderSidebarProfile();
        toast('Buku berhasil dihapus');
      }
    });
  });
}

// ── GOOGLE BOOKS API ──
const GOOGLE_BOOKS_API = 'https://www.googleapis.com/books/v1/volumes';
const GOOGLE_BOOKS_KEY = document.querySelector('meta[name="google-books-key"]')?.content || '';

// Map Google Books categories (English) to our local categories
function mapCategory(categories) {
  if (!categories || !categories.length) return 'Fiksi';
  const cat = categories.join(' ').toLowerCase();
  const mapping = [
    { keys: ['thriller','suspense','crime'], val: 'Thriller' },
    { keys: ['mystery','detective'], val: 'Misteri' },
    { keys: ['romance','love'], val: 'Romansa' },
    { keys: ['science fiction','sci-fi'], val: 'Sci-Fi' },
    { keys: ['fantasy','magic','dragon'], val: 'Fantasi' },
    { keys: ['horror','ghost','supernatural'], val: 'Horror' },
    { keys: ['biography','autobiography','memoir'], val: 'Biografi' },
    { keys: ['history','historical'], val: 'Sejarah' },
    { keys: ['self-help','self help','personal development','psychology','motivation'], val: 'Pengembangan Diri' },
    { keys: ['business','economics','finance'], val: 'Bisnis' },
    { keys: ['poetry','poem'], val: 'Puisi' },
    { keys: ['comics','comic','graphic novel','manga'], val: 'Komik' },
    { keys: ['nonfiction','non-fiction','science','education','reference','philosophy','religion','politics','social'], val: 'Non-Fiksi' },
    { keys: ['fiction','novel','literary'], val: 'Fiksi' },
  ];
  for (const m of mapping) {
    if (m.keys.some(k => cat.includes(k))) return m.val;
  }
  return 'Fiksi';
}

// Force HTTPS on Google Books image URLs (they often return http://)
function fixCoverUrl(url) {
  if (!url) return '';
  return url
    .replace(/^http:\/\//i, 'https://')
    .replace('zoom=1', 'zoom=2')
    .replace('&edge=curl', '');
}

// Extract ISBN from volumeInfo
function extractISBN(info) {
  if (!info.industryIdentifiers) return '';
  const isbn13 = info.industryIdentifiers.find(id => id.type === 'ISBN_13');
  const isbn10 = info.industryIdentifiers.find(id => id.type === 'ISBN_10');
  return isbn13?.identifier || isbn10?.identifier || '';
}

// Parse a single volume from Google Books API into a normalized object
function parseBookVolume(volume) {
  const info = volume.volumeInfo || {};
  const judul = info.title || '';
  const subtitle = info.subtitle ? `: ${info.subtitle}` : '';
  let coverUrl = '';
  if (info.imageLinks) {
    coverUrl = fixCoverUrl(info.imageLinks.thumbnail || info.imageLinks.smallThumbnail || '');
  }
  return {
    judul: judul + subtitle,
    penulis: info.authors?.join(', ') || '',
    tahun: info.publishedDate ? parseInt(info.publishedDate.substring(0, 4)) : '',
    kategori: mapCategory(info.categories),
    halaman: info.pageCount || '',
    deskripsi: info.description || '',
    isbn: extractISBN(info),
    coverUrl,
  };
}

// ── BOOK SEARCH (Autocomplete) ──
let searchDebounceTimer = null;
let currentSearchAbort = null;

function initBookSearch() {
  const input = $('#book-search-input');
  if (!input) return;

  input.addEventListener('input', () => {
    const query = input.value.trim();
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);

    if (query.length < 3) {
      hideSearchResults();
      return;
    }

    searchDebounceTimer = setTimeout(() => searchBooks(query), 500);
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', (e) => {
    const wrapper = $('#book-search-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
      hideSearchResults();
    }
  });
}

// Build a smarter query for Google Books API
function buildSearchQuery(raw) {
  const q = raw.trim();
  // If it looks like an ISBN (digits/dashes, 10-17 chars), search by ISBN
  const isbnClean = q.replace(/[-\s]/g, '');
  if (/^\d{10,13}$/.test(isbnClean)) return `isbn:${isbnClean}`;
  // For short queries (1-2 words), use intitle: for precision
  const wordCount = q.split(/\s+/).length;
  if (wordCount <= 2) return `intitle:${q}`;
  // For longer queries (translated titles, full names), use plain query
  // intitle: is too restrictive for translated/localized book titles
  return q;
}

async function searchBooks(query, retryCount = 0) {
  // Abort previous request if still in-flight
  if (currentSearchAbort) currentSearchAbort.abort();
  currentSearchAbort = new AbortController();

  showSearchSpinner(true);

  try {
    const smartQuery = buildSearchQuery(query);
    const keyParam = GOOGLE_BOOKS_KEY ? `&key=${GOOGLE_BOOKS_KEY}` : '';
    const url = `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(smartQuery)}&maxResults=8&printType=books&orderBy=relevance${keyParam}`;
    console.log('[Alinea] Searching Google Books:', url);

    const response = await fetch(url, {
      signal: currentSearchAbort.signal,
      headers: { 'Accept': 'application/json' },
    });
    console.log('[Alinea] API response status:', response.status);

    if (!response.ok) {
      const errorText = await response.text().catch(() => 'Unknown error');
      console.error('[Alinea] API error response:', errorText);
      throw new Error(`API error: ${response.status}`);
    }

    const data = await response.json();
    console.log('[Alinea] Found', data.totalItems, 'results');

    if (data.totalItems && data.items?.length) {
      const books = data.items.map(parseBookVolume);
      showSearchResults(books);
      showSearchSpinner(false);
      return;
    }

    // If first query returned nothing, try alternative query strategy
    if (retryCount === 0) {
      // If we used intitle:, retry without it. If plain, try intitle:.
      const wordCount = query.trim().split(/\s+/).length;
      const altQuery = wordCount <= 2 ? query.trim() : `intitle:${query.trim()}`;
      console.log('[Alinea] No results, trying alternative query:', altQuery);

      try {
        const fallbackUrl = `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(altQuery)}&maxResults=8&printType=books&orderBy=relevance${keyParam}`;
        const fallbackResp = await fetch(fallbackUrl, {
          signal: currentSearchAbort.signal,
          headers: { 'Accept': 'application/json' },
        });
        if (fallbackResp.ok) {
          const fallbackData = await fallbackResp.json();
          if (fallbackData.totalItems && fallbackData.items?.length) {
            const books = fallbackData.items.map(parseBookVolume);
            showSearchResults(books);
            showSearchSpinner(false);
            return;
          }
        }
      } catch (fallbackErr) {
        console.warn('[Alinea] Fallback search also failed:', fallbackErr.message);
      }
    }

    showSearchResults([]);
    showSearchSpinner(false);
    return;
  } catch (err) {
    if (err.name === 'AbortError') {
      showSearchSpinner(false);
      return;
    }
    console.error('[Alinea] Google Books API error:', err.message, err);
    // Retry once on network/fetch errors
    if (retryCount < 1) {
      console.log('[Alinea] Retrying search in 1s...');
      setTimeout(() => searchBooks(query, retryCount + 1), 1000);
      return;
    }
    showSearchResults(null); // null = error state
    showSearchSpinner(false);
  }
}

function showSearchSpinner(show) {
  const spinner = $('#book-search-spinner');
  if (spinner) spinner.classList.toggle('hidden', !show);
}

function showSearchResults(books) {
  const container = $('#book-search-results');
  if (!container) return;

  // Error state
  if (books === null) {
    container.innerHTML = `
      <div class="p-4 text-center">
        <p class="text-xs text-red-400 font-medium">Gagal mencari buku.</p>
        <p class="text-[11px] text-gray-300 mt-1">Kuota API mungkin habis. Coba lagi nanti atau isi manual di bawah.</p>
      </div>`;
    container.classList.remove('hidden');
    return;
  }

  // No results
  if (books.length === 0) {
    container.innerHTML = `
      <div class="p-4 text-center">
        <p class="text-3xl mb-1.5">📭</p>
        <p class="text-xs text-gray-400 font-medium">Tidak ada hasil ditemukan.</p>
        <p class="text-[11px] text-gray-300 mt-0.5">Coba kata kunci lain, atau isi manual di bawah.</p>
      </div>`;
    container.classList.remove('hidden');
    return;
  }

  // Render results
  container.innerHTML = books.map((book, i) => `
    <button type="button" data-search-result="${i}"
            class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-[#FFDDAF]/15 transition-colors cursor-pointer ${i < books.length - 1 ? 'border-b border-gray-100' : ''} ${i === 0 ? 'rounded-t-2xl' : ''} ${i === books.length - 1 ? 'rounded-b-2xl' : ''}">
      ${book.coverUrl
        ? `<img src="${book.coverUrl}" alt="" class="w-10 h-14 rounded-lg border border-[#444]/20 object-cover flex-shrink-0 bg-gray-100" />`
        : `<div class="w-10 h-14 rounded-lg border border-[#444]/20 bg-gradient-to-br from-[#C7E7FF] to-[#D4F6FF] flex items-center justify-center flex-shrink-0">
            <span class="text-sm font-black text-[#444]/40">${getInitial(book.judul)}</span>
          </div>`
      }
      <div class="flex-1 min-w-0">
        <p class="font-bold text-[13px] text-[#444] truncate leading-tight">${book.judul}</p>
        <p class="text-[11px] text-gray-400 truncate">${book.penulis}${book.tahun ? ` · ${book.tahun}` : ''}</p>
        ${book.isbn ? `<p class="text-[10px] text-gray-300 font-mono mt-0.5">ISBN: ${book.isbn}</p>` : ''}
      </div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="2" class="flex-shrink-0"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
  `).join('');

  container.classList.remove('hidden');

  // Attach click handlers
  container.querySelectorAll('[data-search-result]').forEach(btn => {
    btn.addEventListener('click', () => {
      const idx = parseInt(btn.dataset.searchResult);
      selectSearchResult(books[idx]);
    });
  });
}

function hideSearchResults() {
  const container = $('#book-search-results');
  if (container) { container.classList.add('hidden'); container.innerHTML = ''; }
}

function selectSearchResult(book) {
  hideSearchResults();
  $('#book-search-input').value = '';

  // Fill form fields with highlight animation
  fillField('add-book-judul', book.judul);
  fillField('add-book-penulis', book.penulis);
  if (book.tahun) fillField('add-book-tahun', book.tahun);
  if (book.halaman) fillField('add-book-halaman', book.halaman);
  if (book.isbn) fillField('add-book-isbn-manual', book.isbn);
  selectOption('add-book-kategori', book.kategori);
  $('#add-book-cover-url').value = book.coverUrl;

  // Show preview card
  showBookPreview(book);
}

function fillField(id, value) {
  const el = document.getElementById(id);
  if (el) {
    el.value = value;
    el.classList.add('!border-[#C7E7FF]', '!bg-[#D4F6FF]/10');
    setTimeout(() => el.classList.remove('!border-[#C7E7FF]', '!bg-[#D4F6FF]/10'), 1500);
  }
}

function selectOption(id, value) {
  const el = document.getElementById(id);
  if (!el) return;
  const option = Array.from(el.options).find(o => o.value === value);
  if (option) {
    el.value = value;
    el.classList.add('!border-[#C7E7FF]', '!bg-[#D4F6FF]/10');
    setTimeout(() => el.classList.remove('!border-[#C7E7FF]', '!bg-[#D4F6FF]/10'), 1500);
  }
}

function showBookPreview(book) {
  const preview = $('#book-preview');
  if (!preview) return;

  // Cover
  const coverImg = $('#preview-cover');
  const coverPlaceholder = $('#preview-cover-placeholder');
  if (book.coverUrl) {
    coverImg.src = book.coverUrl;
    coverImg.classList.remove('hidden');
    if (coverPlaceholder) { coverPlaceholder.classList.add('hidden'); coverPlaceholder.classList.remove('flex'); }
  } else {
    coverImg.classList.add('hidden');
    if (coverPlaceholder) {
      coverPlaceholder.classList.remove('hidden');
      coverPlaceholder.classList.add('flex');
      const initEl = $('#preview-cover-initial');
      if (initEl) initEl.textContent = getInitial(book.judul);
    }
  }

  // Text
  const titleEl = $('#preview-title');
  if (titleEl) titleEl.textContent = book.judul;
  const authorEl = $('#preview-author');
  if (authorEl) authorEl.textContent = book.penulis;
  const yearEl = $('#preview-year');
  if (yearEl) yearEl.textContent = book.tahun ? `📅 ${book.tahun}` : '';
  const catEl = $('#preview-category');
  if (catEl) catEl.textContent = `📖 ${book.kategori}`;

  const pagesEl = $('#preview-pages');
  if (pagesEl) {
    if (book.halaman) { pagesEl.textContent = `📄 ${book.halaman} hal`; pagesEl.classList.remove('hidden'); }
    else pagesEl.classList.add('hidden');
  }
  const isbnEl = $('#preview-isbn');
  if (isbnEl) {
    if (book.isbn) { isbnEl.textContent = `ISBN: ${book.isbn}`; isbnEl.classList.remove('hidden'); }
    else isbnEl.classList.add('hidden');
  }
  const descEl = $('#preview-desc');
  if (descEl) descEl.textContent = book.deskripsi ? book.deskripsi.substring(0, 200) + (book.deskripsi.length > 200 ? '...' : '') : '';

  preview.classList.remove('hidden');
}

function hideBookPreview() {
  const preview = $('#book-preview');
  if (preview) preview.classList.add('hidden');
}

function clearBookSelection() {
  hideBookPreview();
  $('#add-book-form')?.reset();
  $('#add-book-cover-url').value = '';
  $('#book-search-input').value = '';
}

// ── ADD BOOK MODAL ──
function openAddBookModal() {
  $('#add-book-modal').classList.remove('hidden');
  $('#add-book-modal').classList.add('flex');
  document.body.style.overflow = 'hidden';
  setTimeout(() => $('#book-search-input')?.focus(), 100);
}
function closeAddBookModal() {
  $('#add-book-modal').classList.add('hidden');
  $('#add-book-modal').classList.remove('flex');
  document.body.style.overflow = '';
  $('#add-book-form')?.reset();
  $('#book-search-input').value = '';
  $('#add-book-cover-url').value = '';
  hideSearchResults();
  hideBookPreview();
}

function handleAddBook(e) {
  e.preventDefault();
  const fd = new FormData(e.target);
  const judul = fd.get('judul')?.trim();
  const penulis = fd.get('penulis')?.trim();
  const isbn = fd.get('isbn')?.trim() || '';
  const tahun = parseInt(fd.get('tahun_terbit'));
  const kategori = fd.get('kategori');
  const coverUrl = fd.get('foto_sampul') || null;
  const halaman = parseInt(fd.get('halaman')) || null;

  if (!judul||!penulis) { toast('Judul dan penulis wajib diisi','error'); return; }

  catalogData.push({
    id: nextCatalogId++,
    judul,
    penulis,
    isbn: isbn || '—',
    tahun_terbit: tahun || new Date().getFullYear(),
    kategori: kategori || 'Fiksi',
    foto_sampul: coverUrl,
    halaman,
    is_available: true,
    status: 'tersedia',
  });

  closeAddBookModal();
  renderCatalog();
  renderSidebarProfile();
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
  // Username
  const el4 = $('#sidebar-username');
  if (el4) el4.textContent = CURRENT_USER.username ? `@${CURRENT_USER.username}` : '';
  // Stats
  const statKoleksi = $('#stat-koleksi');
  if (statKoleksi) statKoleksi.textContent = catalogData.length;
  const statTx = $('#stat-transaksi');
  if (statTx) statTx.textContent = TRANSACTIONS.length;
}

function populateProfileForm() {
  const nama = $('#prof-nama');
  if (nama) nama.value = CURRENT_USER.nama || '';
  const email = $('#prof-email');
  if (email) email.value = CURRENT_USER.email || '';
  const kota = $('#prof-kota');
  if (kota) kota.value = CURRENT_USER.kota || '';
  const telp = $('#prof-telp');
  if (telp) telp.value = CURRENT_USER.no_telp || '';
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

  // Book search (Google Books API autocomplete)
  initBookSearch();
  $('#btn-clear-selection')?.addEventListener('click', clearBookSelection);

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
        // When switched TO text: hide eye-open, show eye-closed
        // When switched TO password: show eye-open, hide eye-closed
        btn.querySelector('.eye-open')?.classList.toggle('hidden', isPassword);
        btn.querySelector('.eye-closed')?.classList.toggle('hidden', !isPassword);
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
  populateProfileForm();
});
