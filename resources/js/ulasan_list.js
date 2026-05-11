/**
 * ulasan_list.js — Alinea Book Browser / Review Listing
 *
 * Browse books → click "Lihat Ulasan" → navigate to ulasan_detail.
 *
 * Data source: BOOKS array below (dummy).
 * When connecting a DB, inject via:
 *   window.__BOOKS_DATA__ = {!! json_encode($books) !!};
 */

// ══════════════════════════════════════
// DUMMY DATA — ganti dengan data dari DB
// ══════════════════════════════════════

const BOOKS = [
  {
    id: 1, judul: 'Pulang', penulis: 'Tere Liye', tahun: 2015,
    rating_avg: 4.6, rating_count: 200,
    sinopsis: 'Bujang, Si Tukang Pukul Keluarga Tong, Mencari Arti Pulang Di Dunia Bayangan Yang Penuh Intrik Dan Pengkhianatan.',
    genres: ['Horror', 'Thriller'],
    gradient_from: '#FFDDAF', gradient_to: '#C7E7FF',
    cover: null,
  },
  {
    id: 2, judul: 'Bumi', penulis: 'Tere Liye', tahun: 2014,
    rating_avg: 4.8, rating_count: 350,
    sinopsis: 'Raib, Ali, dan Seli memulai petualangan luar biasa ke klan-klan misterius di dunia paralel yang penuh keajaiban.',
    genres: ['Fantasy', 'Adventure'],
    gradient_from: '#C7E7FF', gradient_to: '#D4F6FF',
    cover: null,
  },
  {
    id: 3, judul: 'Laut Bercerita', penulis: 'Leila S. Chudori', tahun: 2017,
    rating_avg: 4.7, rating_count: 180,
    sinopsis: 'Kisah aktivis mahasiswa yang menghilang di era Orde Baru, diceritakan lewat suara mereka yang ditenggelamkan laut.',
    genres: ['Sastra', 'Sejarah'],
    gradient_from: '#D4F6FF', gradient_to: '#FFDDAF',
    cover: null,
  },
  {
    id: 4, judul: 'Cantik Itu Luka', penulis: 'Eka Kurniawan', tahun: 2002,
    rating_avg: 4.5, rating_count: 145,
    sinopsis: 'Dewi Ayu bangkit dari kubur dan menyaksikan dunia yang dipenuhi kekerasan, kecantikan, dan kutukan tak berujung.',
    genres: ['Sastra', 'Realisme Magis'],
    gradient_from: '#FFDDAF', gradient_to: '#D4F6FF',
    cover: null,
  },
  {
    id: 5, judul: 'Supernova: Ksatria, Puteri, dan Bintang Jatuh', penulis: 'Dee Lestari', tahun: 2001,
    rating_avg: 4.4, rating_count: 290,
    sinopsis: 'Dua sahabat menulis novel yang mengubah kehidupan orang-orang di sekitarnya lewat perjalanan spiritual dan sains.',
    genres: ['Fiksi', 'Filosofi'],
    gradient_from: '#C7E7FF', gradient_to: '#FFDDAF',
    cover: null,
  },
  {
    id: 6, judul: 'Filosofi Teras', penulis: 'Henry Manampiring', tahun: 2018,
    rating_avg: 4.6, rating_count: 410,
    sinopsis: 'Pengantar filsafat Stoa yang dikemas ringan dan praktis untuk menghadapi kecemasan hidup modern.',
    genres: ['Non-Fiksi', 'Filosofi'],
    gradient_from: '#D4F6FF', gradient_to: '#C7E7FF',
    cover: null,
  },
  {
    id: 7, judul: 'Hujan', penulis: 'Tere Liye', tahun: 2016,
    rating_avg: 4.3, rating_count: 165,
    sinopsis: 'Lail dan Esok, dua remaja yang tumbuh bersama di tengah bencana alam dan teknologi masa depan yang menghapus ingatan.',
    genres: ['Romance', 'Sci-Fi'],
    gradient_from: '#FFDDAF', gradient_to: '#C7E7FF',
    cover: null,
  },
  {
    id: 8, judul: 'Perahu Kertas', penulis: 'Dee Lestari', tahun: 2009,
    rating_avg: 4.2, rating_count: 230,
    sinopsis: 'Kugy dan Keenan mengejar mimpi masing-masing — menulis dongeng dan melukis — sambil tersesat dalam cinta.',
    genres: ['Romance', 'Drama'],
    gradient_from: '#C7E7FF', gradient_to: '#D4F6FF',
    cover: null,
  },
  {
    id: 9, judul: 'Negeri 5 Menara', penulis: 'A. Fuadi', tahun: 2009,
    rating_avg: 4.5, rating_count: 320,
    sinopsis: 'Enam sahabat di pesantren bermimpi mengunjungi menara-menara dunia, diiringi mantra "Man Jadda Wajada".',
    genres: ['Inspirasi', 'Drama'],
    gradient_from: '#D4F6FF', gradient_to: '#FFDDAF',
    cover: null,
  },
  {
    id: 10, judul: 'Ronggeng Dukuh Paruk', penulis: 'Ahmad Tohari', tahun: 1982,
    rating_avg: 4.7, rating_count: 120,
    sinopsis: 'Srintil menjadi ronggeng yang dikagumi, namun terjebak dalam pusaran tragedi politik dan budaya Jawa.',
    genres: ['Sastra', 'Klasik'],
    gradient_from: '#FFDDAF', gradient_to: '#D4F6FF',
    cover: null,
  },
  {
    id: 11, judul: 'Sapiens', penulis: 'Yuval Noah Harari', tahun: 2011,
    rating_avg: 4.8, rating_count: 500,
    sinopsis: 'Menelusuri sejarah umat manusia dari zaman batu hingga era revolusi sains dan apa artinya menjadi manusia.',
    genres: ['Non-Fiksi', 'Sejarah'],
    gradient_from: '#C7E7FF', gradient_to: '#FFDDAF',
    cover: null,
  },
  {
    id: 12, judul: 'Atomic Habits', penulis: 'James Clear', tahun: 2018,
    rating_avg: 4.9, rating_count: 620,
    sinopsis: 'Panduan praktis untuk membangun kebiasaan baik dan menghancurkan kebiasaan buruk lewat perubahan kecil yang konsisten.',
    genres: ['Non-Fiksi', 'Self-Help'],
    gradient_from: '#D4F6FF', gradient_to: '#C7E7FF',
    cover: null,
  },
  {
    id: 13, judul: 'Matahari', penulis: 'Tere Liye', tahun: 2016,
    rating_avg: 4.6, rating_count: 195,
    sinopsis: 'Lanjutan petualangan Raib dan kawan-kawan menghadapi kekuatan gelap yang mengancam seluruh klan paralel.',
    genres: ['Fantasy', 'Adventure'],
    gradient_from: '#FFDDAF', gradient_to: '#C7E7FF',
    cover: null,
  },
  {
    id: 14, judul: 'Dilan 1990', penulis: 'Pidi Baiq', tahun: 2014,
    rating_avg: 4.1, rating_count: 380,
    sinopsis: 'Kisah cinta Dilan dan Milea di Bandung tahun 1990, penuh gombalan khas dan kenangan SMA yang tak terlupakan.',
    genres: ['Romance', 'Drama'],
    gradient_from: '#C7E7FF', gradient_to: '#D4F6FF',
    cover: null,
  },
  {
    id: 15, judul: 'The Alchemist', penulis: 'Paulo Coelho', tahun: 1988,
    rating_avg: 4.5, rating_count: 450,
    sinopsis: 'Santiago, gembala Andalusia, mengejar mimpinya menemukan harta karun di Piramida Mesir dan menemukan makna hidup.',
    genres: ['Fiksi', 'Inspirasi'],
    gradient_from: '#D4F6FF', gradient_to: '#FFDDAF',
    cover: null,
  },
  {
    id: 16, judul: 'Laskar Pelangi', penulis: 'Andrea Hirata', tahun: 2005,
    rating_avg: 4.6, rating_count: 540,
    sinopsis: 'Sepuluh anak dari Belitung berjuang meraih pendidikan dan mimpi di tengah keterbatasan, dipimpin Bu Muslimah yang tangguh.',
    genres: ['Inspirasi', 'Drama'],
    gradient_from: '#FFDDAF', gradient_to: '#C7E7FF',
    cover: null,
  },
];

// ══════════════════════════════════════
// STATE
// ══════════════════════════════════════

const PER_PAGE = 12;
let currentPage = 1;
let debounceTimer = null;

// ══════════════════════════════════════
// DOM REFS
// ══════════════════════════════════════

const grid           = document.getElementById('ulasan-grid');
const pagination     = document.getElementById('ulasan-pagination');
const searchInput    = document.getElementById('ulasan-search-input');
const searchClear    = document.getElementById('ulasan-search-clear');
const filterGenre    = document.getElementById('ulasan-filter-genre');
const filterRating   = document.getElementById('ulasan-filter-rating');
const sortSelect     = document.getElementById('ulasan-sort');
const resultCount    = document.getElementById('ulasan-result-count');
const emptyState     = document.getElementById('ulasan-empty');
const resetBtn       = document.getElementById('ulasan-reset-filters');
const activeFilters  = document.getElementById('ulasan-active-filters');
const scrollTopBtn   = document.getElementById('scrollTopBtn');

// ══════════════════════════════════════
// HELPERS
// ══════════════════════════════════════

function starsHtml(rating) {
  return [1,2,3,4,5].map(s =>
    `<span class="text-[0.82rem] ${s <= Math.round(rating) ? 'text-[#F5C518]' : 'text-[#ddd]'}">★</span>`
  ).join('');
}

function showToast(msg) {
  const t = document.createElement('div');
  t.className = 'toast bg-[#444444] text-white px-6 py-3.5 rounded-xl text-[0.85rem] font-semibold shadow-[0_8px_24px_rgba(0,0,0,0.15)]';
  t.textContent = msg;
  document.getElementById('toastContainer').appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3200);
}

// ══════════════════════════════════════
// POPULATE GENRE OPTIONS FROM DATA
// ══════════════════════════════════════

function populateGenres() {
  const allBooks = window.__BOOKS_DATA__ || BOOKS;
  const genres = [...new Set(allBooks.flatMap(b => b.genres))].sort();
  genres.forEach(g => {
    const opt = document.createElement('option');
    opt.value = g;
    opt.textContent = g;
    filterGenre.appendChild(opt);
  });
}

// ══════════════════════════════════════
// FILTER + SORT + PAGINATE
// ══════════════════════════════════════

function applyFilters(resetPage = true) {
  if (resetPage) currentPage = 1;

  const allBooks = window.__BOOKS_DATA__ || BOOKS;
  const query      = searchInput.value.toLowerCase().trim();
  const genre      = filterGenre.value;
  const minRating  = filterRating.value ? Number(filterRating.value) : 0;
  const sortKey    = sortSelect.value;

  // Toggle clear button
  searchClear.classList.toggle('hidden', !query);

  let result = allBooks.filter(b => {
    const matchSearch = !query
      || b.judul.toLowerCase().includes(query)
      || b.penulis.toLowerCase().includes(query)
      || b.genres.some(g => g.toLowerCase().includes(query));
    const matchGenre = !genre || b.genres.includes(genre);
    const matchRating = b.rating_avg >= minRating;
    return matchSearch && matchGenre && matchRating;
  });

  // Sort
  result = sortBooks(result, sortKey);

  // Paginate
  const totalPages = Math.max(1, Math.ceil(result.length / PER_PAGE));
  if (currentPage > totalPages) currentPage = totalPages;
  const start = (currentPage - 1) * PER_PAGE;
  const paged = result.slice(start, start + PER_PAGE);

  // Render
  resultCount.textContent = result.length;
  renderCards(paged);
  renderPagination(totalPages);
  renderActiveFilters(query, genre, minRating);

  // Empty state
  if (result.length === 0) {
    grid.classList.add('hidden');
    emptyState.classList.remove('hidden');
  } else {
    grid.classList.remove('hidden');
    emptyState.classList.add('hidden');
  }
}

function sortBooks(list, key) {
  const sorted = [...list];
  switch (key) {
    case 'rating-desc':  return sorted.sort((a, b) => b.rating_avg - a.rating_avg);
    case 'rating-asc':   return sorted.sort((a, b) => a.rating_avg - b.rating_avg);
    case 'reviews-desc': return sorted.sort((a, b) => b.rating_count - a.rating_count);
    case 'title-asc':    return sorted.sort((a, b) => a.judul.localeCompare(b.judul));
    case 'title-desc':   return sorted.sort((a, b) => b.judul.localeCompare(a.judul));
    case 'newest':       return sorted.sort((a, b) => b.tahun - a.tahun);
    default:             return sorted;
  }
}

// ══════════════════════════════════════
// RENDER BOOK CARDS
// ══════════════════════════════════════

function renderCards(books) {
  grid.innerHTML = books.map((book, i) => `
    <article class="card-animate group bg-white border-[1.5px] border-[#e8e8e8] rounded-2xl overflow-hidden cursor-pointer
                    flex flex-col hover:border-[#444] hover:-translate-y-1 transition-all duration-300"
             style="animation-delay: ${i * 0.06}s"
             data-book-id="${book.id}">
      
      <!-- Cover -->
      <div class="relative aspect-[4/3] overflow-hidden">
        ${book.cover
          ? `<img src="${book.cover}" alt="Sampul ${book.judul}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />`
          : `<div class="w-full h-full flex items-center justify-center text-3xl font-black text-white/20 group-hover:scale-105 transition-transform duration-500"
                  style="background: linear-gradient(135deg, ${book.gradient_from}, ${book.gradient_to})">
                ${book.judul.charAt(0)}
             </div>`
        }
        <!-- Hover overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
          <span class="text-white text-xs font-semibold bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full">
            Lihat Detail →
          </span>
        </div>
      </div>

      <!-- Info -->
      <div class="p-5 flex flex-col flex-1">
        <h3 class="text-base font-bold text-text leading-tight mb-1 line-clamp-1">${book.judul}</h3>
        <p class="text-[0.78rem] text-text/50 mb-2">${book.penulis}</p>

        <!-- Rating -->
        <div class="flex items-center gap-1.5 mb-3">
          <div class="flex gap-0.5">${starsHtml(book.rating_avg)}</div>
          <span class="text-[0.75rem] font-bold text-text">${book.rating_avg}</span>
          <span class="text-[0.7rem] text-text/35">(${book.rating_count} Ulasan)</span>
        </div>

        <!-- Synopsis -->
        <p class="text-[0.78rem] text-text/55 leading-relaxed mb-4 flex-1 line-clamp-2">${book.sinopsis}</p>

        <!-- Genre pills -->
        <div class="flex gap-1.5 flex-wrap mb-4">
          ${book.genres.map(g => `
            <span class="px-3 py-1 text-[0.68rem] font-medium text-text/70 border border-[#e0e0e0] rounded-full">${g}</span>
          `).join('')}
        </div>

        <!-- CTA -->
        <a href="/ulasan_detail" 
           class="inline-flex items-center gap-2 px-5 py-2 text-[0.8rem] font-bold text-text bg-[#FFDDAF] rounded-full border-[1.5px] border-text
                  hover:bg-amber-300 hover:-translate-y-px transition-all duration-200 self-start"
           onclick="event.stopPropagation()">
          Lihat Ulasan
        </a>
      </div>
    </article>
  `).join('');

  // Click on card = navigate
  grid.querySelectorAll('[data-book-id]').forEach(card => {
    card.addEventListener('click', (e) => {
      if (e.target.closest('a')) return; // let <a> handle itself
      window.location.href = '/ulasan_detail';
    });
  });
}

// ══════════════════════════════════════
// RENDER ACTIVE FILTER CHIPS
// ══════════════════════════════════════

function renderActiveFilters(query, genre, minRating) {
  const chips = [];

  if (query) {
    chips.push(`
      <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#FFDDAF]/40 border border-[#FFDDAF] rounded-full hover:bg-[#FFDDAF] transition-colors"
              data-clear="search">
        🔍 "${query}" <span class="text-text/40">×</span>
      </button>
    `);
  }
  if (genre) {
    chips.push(`
      <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#C7E7FF]/40 border border-[#C7E7FF] rounded-full hover:bg-[#C7E7FF] transition-colors"
              data-clear="genre">
        📂 ${genre} <span class="text-text/40">×</span>
      </button>
    `);
  }
  if (minRating) {
    chips.push(`
      <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#D4F6FF]/40 border border-[#D4F6FF] rounded-full hover:bg-[#D4F6FF] transition-colors"
              data-clear="rating">
        ⭐ ${minRating}+ <span class="text-text/40">×</span>
      </button>
    `);
  }

  activeFilters.innerHTML = chips.join('');

  // Chip click to remove that filter
  activeFilters.querySelectorAll('[data-clear]').forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.dataset.clear;
      if (type === 'search')  { searchInput.value = ''; }
      if (type === 'genre')   { filterGenre.value = ''; }
      if (type === 'rating')  { filterRating.value = ''; }
      applyFilters();
    });
  });
}

// ══════════════════════════════════════
// RENDER PAGINATION
// ══════════════════════════════════════

function renderPagination(totalPages) {
  if (totalPages <= 1) { pagination.innerHTML = ''; return; }

  const btnBase = 'w-9 h-9 rounded-full border-[1.5px] border-[#444] flex items-center justify-center text-sm font-medium transition-all duration-200 cursor-pointer';
  const btnActive = 'bg-[#FFDDAF] text-[#444] scale-105';
  const btnInactive = 'bg-white text-[#444] hover:bg-gray-50 hover:scale-105';

  let html = '';

  // Prev
  html += `<button data-page="prev" ${currentPage === 1 ? 'disabled' : ''}
              class="${btnBase} ${currentPage === 1 ? 'opacity-30 cursor-not-allowed' : btnInactive}">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
           </button>`;

  // Pages (smart ellipsis)
  const pages = getPageNumbers(currentPage, totalPages);
  pages.forEach(p => {
    if (p === '...') {
      html += `<span class="w-9 h-9 flex items-center justify-center text-sm text-text/30">…</span>`;
    } else {
      html += `<button data-page="${p}" class="${btnBase} ${p === currentPage ? btnActive : btnInactive}">${p}</button>`;
    }
  });

  // Next
  html += `<button data-page="next" ${currentPage === totalPages ? 'disabled' : ''}
              class="${btnBase} ${currentPage === totalPages ? 'opacity-30 cursor-not-allowed' : btnInactive}">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
           </button>`;

  pagination.innerHTML = html;

  pagination.querySelectorAll('[data-page]').forEach(btn => {
    btn.addEventListener('click', () => {
      const val = btn.dataset.page;
      if (val === 'prev' && currentPage > 1) currentPage--;
      else if (val === 'next' && currentPage < totalPages) currentPage++;
      else if (val !== 'prev' && val !== 'next') currentPage = Number(val);
      applyFilters(false);
      window.scrollTo({ top: grid.offsetTop - 100, behavior: 'smooth' });
    });
  });
}

function getPageNumbers(current, total) {
  if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
  const pages = [];
  if (current <= 4)       { pages.push(1,2,3,4,5,'...',total); }
  else if (current >= total - 3) { pages.push(1,'...',total-4,total-3,total-2,total-1,total); }
  else                    { pages.push(1,'...',current-1,current,current+1,'...',total); }
  return pages;
}

// ══════════════════════════════════════
// EVENT LISTENERS
// ══════════════════════════════════════

// Debounced search
searchInput.addEventListener('input', () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => applyFilters(), 250);
});

// Clear search
searchClear.addEventListener('click', () => {
  searchInput.value = '';
  applyFilters();
  searchInput.focus();
});

// Keyboard shortcut: "/" to focus search
document.addEventListener('keydown', (e) => {
  if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
    e.preventDefault();
    searchInput.focus();
    showToast('💡 Tekan / untuk mencari buku');
  }
  if (e.key === 'Escape' && document.activeElement === searchInput) {
    searchInput.blur();
  }
});

// Filters & sort
filterGenre.addEventListener('change', () => applyFilters());
filterRating.addEventListener('change', () => applyFilters());
sortSelect.addEventListener('change', () => applyFilters());

// Reset all filters
resetBtn.addEventListener('click', () => {
  searchInput.value = '';
  filterGenre.value = '';
  filterRating.value = '';
  sortSelect.value = 'rating-desc';
  applyFilters();
  showToast('🔄 Filter telah direset');
});

// Scroll-to-top button
window.addEventListener('scroll', () => {
  scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
}, { passive: true });

scrollTopBtn.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ══════════════════════════════════════
// INIT
// ══════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
  populateGenres();
  applyFilters();
});
