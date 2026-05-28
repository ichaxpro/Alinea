/* ═══════════════════════════════════════════
   detail_buku.js — Alinea Book Detail Page
   Data-driven: semua data dari JS, siap integrasi DB
   
   Untuk integrasi database nanti, cukup ganti BOOK_DATA, REVIEWS,
   dan SIMILAR_BOOKS dengan data dari controller via:
   window.__BOOK_DATA__ = {!! json_encode($book) !!};
═══════════════════════════════════════════ */

let REVIEWS = [];
let currentSort = 'newest';
let editingReviewId = null;

function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function getBookId() {
  return String(window.__BOOK_DATA__?.id ?? '');
}

function getBookIdType() {
  return window.__BOOK_DATA__?.book_identifier_type ?? 'db';
}

const SIMILAR_BOOKS = [
  { title:'Bumi', author:'Tere Liye', color:'from-[#C7E7FF] to-[#D4F6FF]', rating:4.8, initial:'B' },
  { title:'Bulan', author:'Tere Liye', color:'from-[#FFDDAF] to-[#D4F6FF]', rating:4.7, initial:'B' },
  { title:'Matahari', author:'Tere Liye', color:'from-[#FFDDAF] to-[#C7E7FF]', rating:4.8, initial:'M' },
  { title:'Bintang', author:'Tere Liye', color:'from-[#D4F6FF] to-[#FFDDAF]', rating:4.6, initial:'B' },
  { title:'Ceros dan Batozar', author:'Tere Liye', color:'from-[#C7E7FF] to-[#FFDDAF]', rating:4.5, initial:'C' },
];

// ══════════════════════════════════════
// HELPERS
// ══════════════════════════════════════

function starsHtml(rating, size = 'text-base') {
  return [1,2,3,4,5].map(s =>
    `<span class="${size} ${s <= Math.round(rating) ? 'text-[#F5C518]' : 'text-[#ddd]'}">★</span>`
  ).join('');
}

function setTextById(id, text) {
  const el = document.getElementById(id);
  if (el) el.textContent = text;
}

function setHtmlById(id, html) {
  const el = document.getElementById(id);
  if (el) el.innerHTML = html;
}

// Hitung ulang statistik rating dari array REVIEWS lokal dan update UI langsung
function refreshRatingStats() {
  const book  = window.__BOOK_DATA__;
  const total = REVIEWS.length;

  const avg = total > 0
    ? REVIEWS.reduce((sum, r) => sum + r.rating, 0) / total
    : 0;

  const dist = {};
  REVIEWS.forEach(r => { dist[r.rating] = (dist[r.rating] || 0) + 1; });

  book.rating_avg          = Math.round(avg * 10) / 10;
  book.rating_count        = total;
  book.rating_distribution = dist;

  // Update header rating badge
  setHtmlById('bookRating', `
    <div class="flex gap-0.5">${starsHtml(book.rating_avg)}</div>
    <span class="text-[0.85rem] text-text/60">
      <strong class="text-text font-bold">${book.rating_avg}</strong> (${book.rating_count} Ulasan)
    </span>
  `);

  // Update rating breakdown bars
  renderRatingBreakdown(book);
}

function updateUlasanButton(hasReviewed, myReviewId) {
  const btn = document.getElementById('tulisUlasanBtn');
  if (!btn) {
    return;
  }

  if (hasReviewed && myReviewId) {
    btn.className = 'inline-flex items-center gap-2 px-7 py-2.5 text-[0.85rem] font-bold text-text bg-white rounded-full border-[1.5px] border-[#ddd] transition-all duration-200 hover:-translate-y-px hover:border-text hover:shadow-[0_4px_12px_rgba(0,0,0,0.08)]';
    btn.innerHTML = `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M14.5 2.5a2.121 2.121 0 0 1 3 3L6 17l-4 1 1-4L14.5 2.5z"/></svg>
      Edit Ulasan`;

    btn.dataset.myReviewId = myReviewId;
  } else {
    btn.className = 'inline-flex items-center gap-2 px-7 py-2.5 text-[0.85rem] font-bold text-text bg-accent rounded-full border-[1.5px] border-text transition-all duration-200 hover:-translate-y-px hover:shadow-[0_4px_12px_rgba(0,0,0,0.1)]';
    btn.innerHTML = `
      <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M14.5 2.5a2.121 2.121 0 0 1 3 3L6 17l-4 1 1-4L14.5 2.5z"/></svg>
      Tulis Ulasan
    `;
    delete btn.dataset.myReviewId;
  }
}

// ══════════════════════════════════════
// RENDER BOOK DETAIL
// ══════════════════════════════════════

function renderBookDetail(book) {
  // Page title & meta
  document.title = `${book.judul} — ${book.penulis} | Alinea`;
  const metaDesc = document.querySelector('meta[name="description"]');
  if (metaDesc) metaDesc.content = `Detail buku ${book.judul} karya ${book.penulis}. Baca ulasan, lihat rating, dan pinjam buku di Alinea.`;

  // Cover
  const cover = document.getElementById('bookCover');
  if (cover && book.foto_sampul) {
    cover.innerHTML = `<img src="${book.foto_sampul}" alt="Sampul ${book.judul}" class="w-full h-full object-cover" />`;
  }

  // Basic info
  setTextById('bookCategory', book.kategori);
  setTextById('bookTitle', book.judul);
  setTextById('bookMeta', `${book.penulis} • ${book.tahun_terbit} • ${book.jumlah_halaman} Halaman`);
  setTextById('bookSynopsis', book.sinopsis);

  // Modal title
  setTextById('modalBookTitle', book.judul);

  // Pinjam Modal Details
  setTextById('pinjamBookTitle', book.judul);
  setTextById('pinjamBookWriter', book.penulis);
  // Owner is dynamically set upon selection
  const pinjamCover = document.getElementById('pinjamBookCover');
  if (pinjamCover && book.foto_sampul) {
    pinjamCover.innerHTML = `<img src="${book.foto_sampul}" alt="Sampul ${book.judul}" class="w-full h-full object-cover rounded-xl" />`;
  } else if (pinjamCover) {
    pinjamCover.innerHTML = `<div class="absolute inset-0 rounded-xl shadow-[inset_0_0_0_1px_rgba(0,0,0,0.06)] pointer-events-none"></div>`;
  }

  // Rating
  setHtmlById('bookRating', `
    <div class="flex gap-0.5">${starsHtml(book.rating_avg)}</div>
    <span class="text-[0.85rem] text-text/60">
      <strong class="text-text font-bold">${book.rating_avg}</strong> (${book.rating_count} Ulasan)
    </span>
  `);

  // Genres
  setHtmlById('bookGenres', book.genres.map(g =>
    `<span class="px-6 py-1.5 text-[0.8rem] font-medium text-text border-[1.5px] border-[#ddd] rounded-full transition-all duration-200 hover:border-text hover:bg-[#FBFBFB]">${g}</span>`
  ).join(''));

  // Info grid
  const owners = window.__BOOK_DATA__.owners ?? [];
  const isAvailable = owners.length > 0;

  const statusColor = isAvailable ? 'text-[#22c55e]' : 'text-red-500';
  const statusText = isAvailable ? 'Tersedia' : 'Tidak Tersedia';

  const infoItems = [
    ['Penerbit', book.penerbit],
    ['ISBN', book.isbn],
    ['Bahasa', book.bahasa],
    ['Ketersediaan', `<span class="font-semibold ${statusColor}">${statusText}</span>`],
  ];
  setHtmlById('bookInfoGrid', infoItems.map(([label, value]) => `
    <div>
      <span class="block text-[0.72rem] font-semibold text-[#444444]/45 uppercase tracking-[0.06em] mb-0.5">${label}</span>
      <span class="text-[0.85rem] font-medium text-[#444444]">${value}</span>
    </div>
  `).join(''));

  const pinjamBtn = document.getElementById('pinjamBtn');
  if (!isAvailable) {
    pinjamBtn.disabled = true;
    pinjamBtn.style.opacity = '0.45';
    pinjamBtn.style.cursor = 'not-allowed';
    pinjamBtn.title = 'Belum ada pemilik yang meminjamkan buku ini.'
  }
}

// ══════════════════════════════════════
// RENDER RATING BREAKDOWN
// ══════════════════════════════════════

function renderRatingBreakdown(book) {
  const total = book.rating_count;

  setHtmlById('ratingAvgBlock', `
    <span class="block text-5xl font-black text-[#444444] leading-none">${book.rating_avg.toFixed(1)}</span>
    <div class="my-2 md:mt-2 md:mb-1 flex text-[1.1rem]">${starsHtml(book.rating_avg, 'text-[1.1rem]')}</div>
    <span class="text-[0.78rem] text-[#444444]/50">${total} ulasan</span>
  `);

  setHtmlById('ratingBarsBlock', [5,4,3,2,1].map(star => {
    const count = book.rating_distribution[star] || 0;
    const pct = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
    return `
      <div class="flex items-center gap-2.5">
        <span class="text-[0.78rem] font-semibold text-[#444444] w-3.5 text-right">${star}</span>
        <div class="flex-1 h-2 bg-[#e8e8e8] rounded-full overflow-hidden">
          <div class="h-full bg-[#F5C518] rounded-full transition-all duration-1000 ease-in-out" style="width: ${pct}%"></div>
        </div>
        <span class="text-[0.72rem] text-[#444444]/45 w-7">${count}</span>
      </div>`;
  }).join(''));
}

// ══════════════════════════════════════
// RENDER REVIEWS (paginated)
// ══════════════════════════════════════

let currentReviewPage = 0;
const REVIEWS_PER_PAGE = 4;
let pickedRating = 0;
let helpfulVotes = new Set();

function renderReviews(reset = false) {
  const list = document.getElementById('reviewsList');
  if (reset) { list.innerHTML = ''; currentReviewPage = 0; }

  const start = currentReviewPage * REVIEWS_PER_PAGE;
  const end = Math.min(start + REVIEWS_PER_PAGE, REVIEWS.length);

  REVIEWS.slice(start, end).forEach((r, i) => {
    const voted = helpfulVotes.has(r.id);
    const card = document.createElement('div');
    card.className = 'bg-white rounded-[20px] p-7 md:p-8 mb-4 animate-fade-in-up';
    card.style.animationDelay = `${i * 0.08}s`;

    card.innerHTML = `
      <div class="flex items-center justify-between mb-3.5">
        <div class="flex items-center gap-3">
          ${r.avatar_url
            ? `<img src="${r.avatar_url}" alt="${r.name}" class="w-10 h-10 rounded-full object-cover shrink-0" />`
            : `<div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#D4F6FF] flex items-center justify-center text-[0.85rem] font-bold text-[#444444] shrink-0">${r.initial}</div>`
          }
          <div>
            <span class="text-[0.9rem] font-bold text-[#444444]">${r.name}</span>
            <span class="text-[0.75rem] text-[#444444]/40 ml-2">${r.date}</span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="flex gap-0.5 text-[0.85rem]">${starsHtml(r.rating, 'text-[0.85rem]')}</div>
          ${window.__USER__ && r.user_id == window.__USER__.id ? `
            <button class="btn-edit-review text-[0.72rem] font-semibold text-[#444444]/50 hover:text-[#444444] transition-colors px-2 py-0.5 rounded-md hover:bg-[#f5f5f5]" data-id="${r.id}">Edit</button>
            <button class="btn-delete-review text-[0.72rem] font-semibold text-red-400 hover:text-red-600 transition-colors px-2 py-0.5 rounded-md hover:bg-red-50" data-id="${r.id}">Hapus</button>
          ` : ''}
        </div>
      </div>
      <p class="text-[0.88rem] leading-[1.75] text-[#444444]/80 mb-4">${r.text}</p>
      <div class="flex items-center justify-between">
        <span class="text-[0.78rem] text-[#444444]/35 italic">Apakah ulasan ini membantu?</span>
        <button class="btn-helpful inline-flex items-center gap-1.5 px-4 py-1.5 text-[0.78rem] font-semibold text-[#444444] border ${voted ? 'border-[#FFDDAF] bg-[#FFDDAF]' : 'border-[#ddd] bg-white'} rounded-full transition-all duration-200 hover:border-[#444444] hover:bg-[#FBFBFB]" data-id="${r.id}">
          Membantu (${r.helpful})
        </button>
      </div>`;
    list.appendChild(card);
  });

  currentReviewPage++;
  const btn = document.getElementById('loadMoreReviews');
  btn.style.display = end >= REVIEWS.length ? 'none' : 'inline-block';
}

async function loadReviews() {
  const bookId = getBookId();
  if (!bookId) return;

  try {
    const res  = await fetch(`/api/reviews/${bookId}`);
    const data = await res.json();

    REVIEWS = data.reviews ?? [];

    // Restore the current user's voted state from server data
    helpfulVotes.clear();
    REVIEWS.forEach(r => { if (r.my_vote) helpfulVotes.add(r.id); });

    updateUlasanButton(data.has_reviewed, data.my_review_id);

    const book = window.__BOOK_DATA__;
    book.rating_avg          = data.rating_avg;
    book.rating_count        = data.rating_count;
    book.rating_distribution = data.rating_distribution;

    // Update header rating dengan data real dari DB (bukan dari Google Books API)
    setHtmlById('bookRating', `
      <div class="flex gap-0.5">${starsHtml(book.rating_avg)}</div>
      <span class="text-[0.85rem] text-text/60">
        <strong class="text-text font-bold">${book.rating_avg}</strong> (${book.rating_count} Ulasan)
      </span>
    `);

    renderRatingBreakdown(book);
    renderReviews(true);
  } catch (err) {
    console.error('Gagal memuat ulasan: ', err);
  }
}

// ══════════════════════════════════════
// RENDER SIMILAR BOOKS
// ══════════════════════════════════════

function renderSimilarBooks() {
  const grid = document.getElementById('similarGrid');
  grid.innerHTML = SIMILAR_BOOKS.map(b => `
    <div class="cursor-pointer transition-transform duration-200 hover:-translate-y-1" role="button" tabindex="0">
      <div class="w-full aspect-[2/3] rounded-xl flex items-center justify-center text-2xl font-black text-[#444444]/20 mb-2.5 overflow-hidden bg-gradient-to-br ${b.color}">${b.initial}</div>
      <h4 class="text-[0.82rem] font-bold text-[#444444] mb-0.5 whitespace-nowrap overflow-hidden text-ellipsis">${b.title}</h4>
      <p class="text-[0.72rem] text-[#444444]/50">${b.author}</p>
      <p class="text-[0.72rem] text-[#F5C518] mt-1">★ ${b.rating}</p>
    </div>
  `).join('');
}

// ══════════════════════════════════════
// RENDER OWNERS TABLE
// ══════════════════════════════════════

// Mocking current user for demonstration purposes. In production, this can come from window.__USER_DATA__
const CURRENT_USER = {
  name: 'Current User',
  domicile: 'Surabaya' 
};

function renderOwnersTable(book, filterLoc = 'all') {
  const tbody = document.getElementById('ownersTableBody');
  if (!tbody || !book.owners) return;
  
  // Filter by location
  let filteredOwners = [...book.owners];
  if (filterLoc !== 'all') {
    filteredOwners = filteredOwners.filter(owner => owner.location === filterLoc);
  }
  
  // Sort so that owners with the same domicile as the current user appear first
  filteredOwners.sort((a, b) => {
    const aMatch = a.location === CURRENT_USER.domicile ? 1 : 0;
    const bMatch = b.location === CURRENT_USER.domicile ? 1 : 0;
    return bMatch - aMatch;
  });

  if (filteredOwners.length === 0) {
    tbody.innerHTML = `<tr><td colspan="3" class="py-6 text-center text-[0.85rem] text-[#444444]/60">Tidak ada pemilik di lokasi ini yang memiliki buku.</td></tr>`;
    return;
  }
  
  tbody.innerHTML = filteredOwners.map(owner => `
    <tr class="border-b-[1.5px] border-[#eee] last:border-0 hover:bg-[#FBFBFB] transition-colors">
      <td class="py-4 px-4">
        <div class="flex items-center gap-3">
          ${owner.avatar_url
            ? `<img src="${owner.avatar_url}" alt="${owner.name}" class="w-8 h-8 rounded-full object-cover shrink-0" />`
            : `<div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#D4F6FF] flex items-center justify-center text-[0.7rem] font-bold text-[#444444]">${owner.name[0].toUpperCase()}</div>`
          }
          <span class="text-[0.9rem] font-semibold text-[#444444]">${owner.name}</span>
        </div>
      </td>
      <td class="py-4 px-4 text-[0.85rem] text-[#444444]/70">${owner.location}</td>
      <td class="py-4 px-4 text-center">
        <button class="btn-pilih-owner px-4 py-1.5 text-[0.8rem] font-bold text-[#444444] bg-white border-[1.5px] border-[#ddd] rounded-full transition-all duration-200 hover:border-[#444444] hover:bg-[#FBFBFB]" data-name="${owner.name}" data-pbid="${owner.personal_book_id}">Pilih</button>
      </td>
    </tr>
  `).join('');
}

// ══════════════════════════════════════
// EVENT HANDLERS
// ══════════════════════════════════════

// Edit review
document.getElementById('reviewsList').addEventListener('click', async (e) => {
  const btn = e.target.closest('.btn-edit-review');
  if (!btn) return;

  const id = Number(btn.dataset.id);
  const review = REVIEWS.find(r => r.id === id);
  if (!review) return;

  openModal(review);
});

document.getElementById('reviewsList').addEventListener('click', async (e) => {
  const btn = e.target.closest('.btn-delete-review');
  if (!btn) return;
  const id = Number(btn.dataset.id);
  
  if (!confirm('Hapus ulasan ini? Tindakan ini tidak bisa dibatalkan.')) return;
  btn.disabled    = true;
  btn.textContent = 'Menghapus...';
  try {
    const res = await fetch(`/api/reviews/${id}`, {
      method:  'DELETE',
      headers: { 'X-CSRF-TOKEN': getCsrf() },
    });
    const data = await res.json();
    if (!res.ok) {
      showToast(data.message ?? 'Gagal menghapus ulasan.');
      btn.disabled    = false;
      btn.textContent = 'Hapus';
      return;
    }

    REVIEWS = REVIEWS.filter(r => r.id !== id);
    updateUlasanButton(false, null);
    refreshRatingStats();
    renderReviews(true);
    showToast('🗑️ Ulasan berhasil dihapus.');
  } catch (err) {
    showToast('Terjadi kesalahan jaringan.');
    console.error(err);
    btn.disabled    = false;
    btn.textContent = 'Hapus';
  }
});

// Helpful vote toggle
const helpfulPending = new Set(); // guard against double-click while fetch is in flight
document.getElementById('reviewsList').addEventListener('click', async (e) => {
  const btn = e.target.closest('.btn-helpful');
  if (!btn) return;

  const id = Number(btn.dataset.id);
  if (helpfulPending.has(id)) return;

  const review = REVIEWS.find(r => r.id === id);
  if (!review) return;

  const alreadyVoted = helpfulVotes.has(id);

  // Optimistic UI
  if (alreadyVoted) {
    helpfulVotes.delete(id);
    review.helpful--;
    btn.classList.remove('border-[#FFDDAF]', 'bg-[#FFDDAF]');
    btn.classList.add('border-[#ddd]', 'bg-white');
  } else {
    helpfulVotes.add(id);
    review.helpful++;
    btn.classList.remove('border-[#ddd]', 'bg-white');
    btn.classList.add('border-[#FFDDAF]', 'bg-[#FFDDAF]');
  }
  btn.textContent = `Membantu (${review.helpful})`;

  helpfulPending.add(id);
  try {
    const res  = await fetch(`/api/reviews/${id}/helpful`, {
      method:  'POST',
      headers: { 'X-CSRF-TOKEN': getCsrf() },
    });
    const data = await res.json();

    // Reconcile with server's authoritative count + voted state
    review.helpful   = data.helpful;
    review.my_vote   = data.voted;
    if (data.voted) { helpfulVotes.add(id); } else { helpfulVotes.delete(id); }
    btn.textContent = `Membantu (${data.helpful})`;

  } catch (err) {
    // Rollback optimistic update on network error
    if (alreadyVoted) {
      helpfulVotes.add(id); review.helpful++;
      btn.classList.remove('border-[#ddd]', 'bg-white');
      btn.classList.add('border-[#FFDDAF]', 'bg-[#FFDDAF]');
    } else {
      helpfulVotes.delete(id); review.helpful--;
      btn.classList.remove('border-[#FFDDAF]', 'bg-[#FFDDAF]');
      btn.classList.add('border-[#ddd]', 'bg-white');
    }
    btn.textContent = `Membantu (${review.helpful})`;
    console.error('Gagal vote helpful', err);
  } finally {
    helpfulPending.delete(id);
  }
});

// Load more
document.getElementById('loadMoreReviews').addEventListener('click', () => renderReviews());

// Review modal
const modalOverlay = document.getElementById('reviewModalOverlay');
function openModal(reviewToEdit = null) { 
  const user = window.__USER__;
  if (user) {
    const avatarEl = document.getElementById('modalUserAvatar');
    if (user.avatar_url) {
      avatarEl.innerHTML = `<img src="${user.avatar_url}" alt="${user.name}" class="w-full h-full object-cover rounded-full" />`;
    } else {
      avatarEl.textContent = user.name[0].toUpperCase();
    }
    document.getElementById('modalUserName').textContent = user.name;
  }

  if (reviewToEdit) {
    editingReviewId = reviewToEdit.id;
    document.querySelector('#reviewModal h3').textContent = 'Edit Ulasan';
    document.getElementById('submitReviewBtn').textContent = 'Simpan Perubahan';
    document.getElementById('reviewText').value = reviewToEdit.text;

    pickedRating = reviewToEdit.rating;
    starPicker.querySelectorAll('.pick-star').forEach(s => s.classList.toggle('active', Number(s.dataset.val) <= pickedRating));
  } else {
    editingReviewId = null;
    document.querySelector('#reviewModal h3').textContent = 'Tulis Ulasan';
    document.getElementById('submitReviewBtn').textContent = 'Kirim Ulasan';
    document.getElementById('reviewText').value = '';
    pickedRating = 0;
    starPicker.querySelectorAll('.pick-star').forEach(s => {
      s.classList.remove('active');
      s.style.color = '#ddd';
    });
  }
  modalOverlay.classList.add('active'); 
  document.body.style.overflow = 'hidden'; 
}

function closeModal() { 
  modalOverlay.classList.remove('active'); 
  document.body.style.overflow = ''; 
  editingReviewId = null;
}

document.getElementById('tulisUlasanBtn').addEventListener('click', () => {
  if (!window.__AUTH__) {
    showToast('Kamu harus login untuk menulis ulasan.');
    setTimeout(() => window.location.href = '/login', 1500);
    return;
  }

  const btn = document.getElementById('tulisUlasanBtn');
  const myReviewId = Number(btn.dataset.myReviewId);

  if (myReviewId) {
    const myReview = REVIEWS.find(r => r.id === myReviewId);
    if (myReview) {
      openModal(myReview);
    } else {
      openModal();
    }
  } else {
    openModal();
  }
});

document.getElementById('reviewModalClose').addEventListener('click', closeModal);
modalOverlay.addEventListener('click', e => { if (e.target === modalOverlay) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// Star picker
const starPicker = document.getElementById('starPicker');
starPicker.querySelectorAll('.pick-star').forEach(star => {
  star.addEventListener('click', () => {
    pickedRating = Number(star.dataset.val);
    starPicker.querySelectorAll('.pick-star').forEach(s =>
      s.classList.toggle('active', Number(s.dataset.val) <= pickedRating)
    );
  });
  star.addEventListener('mouseenter', () => {
    const val = Number(star.dataset.val);
    starPicker.querySelectorAll('.pick-star').forEach(s =>
      s.style.color = Number(s.dataset.val) <= val ? '#F5C518' : '#ddd'
    );
  });
});
starPicker.addEventListener('mouseleave', () => {
  starPicker.querySelectorAll('.pick-star').forEach(s =>
    s.style.color = s.classList.contains('active') ? '#F5C518' : '#ddd'
  );
});

// Submit review
document.getElementById('submitReviewBtn').addEventListener('click', async () => {
  const text = document.getElementById('reviewText').value.trim();
  if (!text || pickedRating === 0) { showToast('Lengkapi semua field dan pilih rating'); return; }

  const btn = document.getElementById('submitReviewBtn');
  btn.disabled = true;
  btn.textContent = 'Mengirim...';
  
  try {
    let res, data;

    if (editingReviewId) {
      res = await fetch(`/api/reviews/${editingReviewId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCsrf(),
        },
        body: JSON.stringify({rating: pickedRating, ulasan: text}),
      });
    } else {
       res = await fetch('/api/reviews', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCsrf(),
        },
        body: JSON.stringify({
          book_identifier: getBookId(),
          book_identifier_type: getBookIdType(),
          rating: pickedRating,
          ulasan: text,
        }),
      });
    }

    data = await res.json();

    if (!res.ok) {
      showToast(`${data.message ?? 'Gagal mengirim ulasan.'}`);
      return;
    }

    if (editingReviewId) {
      const idx = REVIEWS.findIndex(r => r.id === editingReviewId);
      if (idx !== -1) REVIEWS[idx] = data.review;
      showToast('Ulasan berhasil diperbarui');
    } else {
      REVIEWS.unshift(data.review);
      updateUlasanButton(true, data.review.id);
      showToast('Ulasan berhasil dikirim');
    }

    // Hitung ulang statistik rating dari array lokal langsung
    refreshRatingStats();
    renderReviews(true);

    closeModal();
    document.getElementById('reviewText').value = '';
    pickedRating = 0;
    starPicker.querySelectorAll('.pick-star').forEach(s => s.classList.remove('active'));
    document.getElementById('reviewsSection').scrollIntoView({ behavior: 'smooth' });
  } catch (err) {
    showToast('Terjadi kesalahan jaringan.');
    console.error(err);
  } finally {
    btn.disabled = false;
    btn.textContent = editingReviewId ? 'Simpan Perubahan' : 'Kirim Ulasan';
  }
});

// Save button
document.getElementById('simpanBtn').addEventListener('click', async () => {
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
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"').content,
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
      showToast('Buku disimpan di Dashboard', 'success');
    } else {
      btn.classList.remove('saved');
      showToast('Buku dihapus dari Dashboard', 'info');
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

// Owners modal
const ownersModalOverlay = document.getElementById('ownersModalOverlay');
function openOwnersModal() { ownersModalOverlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeOwnersModal() { ownersModalOverlay.classList.remove('active'); document.body.style.overflow = ''; }

// Pinjam button modal
const pinjamModalOverlay = document.getElementById('pinjamModalOverlay');
function openPinjamModal() { pinjamModalOverlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
function closePinjamModal() { pinjamModalOverlay.classList.remove('active'); document.body.style.overflow = ''; }

// Handle Pinjam button click: show owners first
document.getElementById('pinjamBtn').addEventListener('click', () => {
  renderOwnersTable(window.__BOOK_DATA__);
  openOwnersModal();
});

// Close listeners for owners modal
document.getElementById('ownersModalClose').addEventListener('click', closeOwnersModal);
if (ownersModalOverlay) {
  ownersModalOverlay.addEventListener('click', e => { if (e.target === ownersModalOverlay) closeOwnersModal(); });
}

// Handle Location Filter Dropdown
const lokasiFilter = document.getElementById('lokasiFilter');
if (lokasiFilter) {
  lokasiFilter.addEventListener('change', (e) => {
    renderOwnersTable(window.__BOOK_DATA__, e.target.value);
  });
}

// Close listeners for pinjam modal
document.getElementById('pinjamModalClose').addEventListener('click', closePinjamModal);
if (pinjamModalOverlay) {
  pinjamModalOverlay.addEventListener('click', e => { if (e.target === pinjamModalOverlay) closePinjamModal(); });
}

document.addEventListener('keydown', e => { 
  if (e.key === 'Escape') {
    if (ownersModalOverlay && ownersModalOverlay.classList.contains('active')) closeOwnersModal();
    if (pinjamModalOverlay && pinjamModalOverlay.classList.contains('active')) closePinjamModal();
  }
});

let selectedPersonalBookId = null;

// Handle owner selection
document.getElementById('ownersTableBody').addEventListener('click', (e) => {
  const btn = e.target.closest('.btn-pilih-owner');
  if (!btn) return;
  
  const selectedOwnerName = btn.dataset.name;
  selectedPersonalBookId = btn.dataset.pbid;
  
  // Update pinjam modal with selected owner
  setTextById('pinjamBookOwner', selectedOwnerName);
  
  closeOwnersModal();
  openPinjamModal();
});

// Submit pinjam request
document.getElementById('submitPinjamBtn').addEventListener('click', async () => {
  const durasi = document.getElementById('durasiPeminjaman').value.trim();
  const titik = document.getElementById('titikTemu').value.trim();
  if (!durasi || !titik) { showToast('⚠️ Lengkapi durasi dan titik temu'); return; }
  
  if (!selectedPersonalBookId) { showToast('⚠️ Pilih pemilik buku terlebih dahulu.'); return; }
  
  const btn = document.getElementById('submitPinjamBtn');
  btn.disabled = true;
  btn.textContent = 'Memproses...';

  try {
      const response = await fetch('/transactions', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
              'Accept': 'application/json'
          },
          body: JSON.stringify({ 
              book_id: selectedPersonalBookId, 
              titik_temu: titik, 
              durasi_hari: parseInt(durasi) 
          })
      });

      if (response.ok) {
          closePinjamModal();
          document.getElementById('durasiPeminjaman').value = '';
          document.getElementById('titikTemu').value = '';
          showToast('📚 Permintaan peminjaman diajukan! Cek notifikasi secara berkala.');
      } else {
          const err = await response.json();
          showToast('⚠️ Gagal mengirim pengajuan: ' + (err.message || 'Error'));
      }
  } catch (error) {
      showToast('⚠️ Terjadi kesalahan jaringan.');
  } finally {
      btn.disabled = false;
      btn.textContent = 'Kirim Pengajuan';
  }
});

// Sort reviews
document.getElementById('sortSelect').addEventListener('change', function() {
  currentSort = this.value;
  const sorters = {
    newest: (a,b) => b.id - a.id, oldest: (a,b) => a.id - b.id,
    highest: (a,b) => b.rating - a.rating, lowest: (a,b) => a.rating - b.rating,
    helpful: (a,b) => b.helpful - a.helpful,
  };
  REVIEWS.sort(sorters[this.value] || sorters.newest);
  renderReviews(true);
});

// Toast
function showToast(msg) {
  const t = document.createElement('div');
  t.className = 'toast bg-[#444444] text-white px-6 py-3.5 rounded-xl text-[0.85rem] font-semibold shadow-[0_8px_24px_rgba(0,0,0,0.15)]';
  t.textContent = msg;
  document.getElementById('toastContainer').appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3200);
}

// ══════════════════════════════════════
// INIT — render semua dari data
// ══════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
  // Kalau nanti data dari DB, cukup ganti: const book = window.__BOOK_DATA__ || BOOK_DATA;
  const book = window.__BOOK_DATA__;

  renderBookDetail(book);
  renderRatingBreakdown(book);
  loadReviews();
  renderSimilarBooks();

  async function initBookmarkState() {
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
        btn.classList.add('saved');
      } else {
        btn.classList.remove('saved');
      }
    } catch (e) {
      console.error('Gagal cek status bookmark: ', e);
    }
  }

  initBookmarkState();
});