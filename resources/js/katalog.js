/**
 * katalog.js — Alinea Book Browser / Review Listing
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
const GOOGLE_BOOKS_API = 'https://www.googleapis.com/books/v1/volumes';
const GOOGLE_BOOKS_KEY = document.querySelector('meta[name="google-books-key"]')?.content || '';

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
    rating_avg: 0,
    rating_count: 0,
    sinopsis: info.description || '',
    genres: [mapCategory(info.categories)],
    cover: coverUrl || null,
    gradient_from: '#C7E7FF',
    gradient_to: '#FFDDAF',
  };
}

async function fetchGoogleBooks(query, maxResults = 40) {
    const keyParam = GOOGLE_BOOKS_KEY ? `&key=${GOOGLE_BOOKS_KEY}` : '';
    const url = `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&maxResults=${maxResults}&printType=books&orderBy=relevance${keyParam}`;
    const res = await fetch(url, {headers: {'Accept': 'application/json'}});
    if(!res.ok) throw new Error(`API error: ${res.status}`);
    const data = await res.json();
    return (data.items || []).map((v, i) => ({...parseBookVolume(v), id:i + 1, google_id: v.id}));
}

async function fetchOpenLibrary(query, limit = 40) {
  const url = `https://openlibrary.org/search.json?q=${encodeURIComponent(query)}&limit=${limit}`;
  const res = await fetch(url);
  if (!res.ok) throw new Error(`Open Library error: ${res.status}`);
  const data = await res.json();
  return (data.docs || []).map((b, i) => ({
    id: i + 1,
    judul: b.title,
    penulis: b.author_name?.join(', ') || '',
    tahun: b.first_publish_year || '',
    rating_avg: 0,
    rating_count: 0,
    sinopsis: b.description || b.subtitle || '',
    genres: b.subject?.slice(0, 3) || ['Fiksi'],
    cover: b.cover_i ? `https://covers.openlibrary.org/b/id/${b.cover_i}-L.jpg` : null,
    gradient_from: '#C7E7FF',
    gradient_to: '#FFDDAF',
  }));
}

async function fetchBooks(query) {
  const featured = window.__FEATURED_BOOKS__ || [];
  const seen = new Set(featured.map(b => b.judul.toLowerCase()));
  let apiBooks = [];

  try {
    const books = await fetchGoogleBooks(query);
    if (books.length > 0) apiBooks = books;
  } catch (e) {
    console.warn('Google Books error:', e);
  }

  if (apiBooks.length === 0) {
    console.warn('Google Books returned 0 results, falling back to Open Library');
    try {
      apiBooks = await fetchOpenLibrary(query);
    } catch (e2) {
      console.warn('Open Library also failed');
    }
  }

  const merged = [...featured];
  let nextId = merged.length + 1;
  for (const b of apiBooks) {
    if (!seen.has(b.judul.toLowerCase())) {
      seen.add(b.judul.toLowerCase());
      merged.push({ ...b, id: nextId++ });
    }
  }
  return merged;
}

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

async function fetchRatingStats(ids) {
  if (!ids.length) return {};
  try {
    const params = new URLSearchParams();
    ids.forEach(id => params.append('ids[]', id));
    const res = await fetch(`/api/reviews/stats?${params}`);
    const data = await res.json();
    return data.stats ?? {};
  } catch (e) {
    console.warn('Gagal fetch rating stats: ', e);
    return {};
  }
}

// ══════════════════════════════════════
// POPULATE GENRE OPTIONS FROM DATA
// ══════════════════════════════════════

function populateGenres() {
  const allBooks = window.__BOOKS_DATA__ || [];
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

  const allBooks = window.__BOOKS_DATA__ || [];
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
             data-book-id="${book.id}"
             data-google-id="${book.google_id || ''}">
      
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
        <a href="/detail-buku/${book.id || book.google_id}" 
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
      const id = card.dataset.bookId;
      const googleId = card.dataset.googleId;
      const target = googleId || id;
      if (target) window.location.href = '/detail-buku/' + target;
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

// Debounced search — fetches from Google Books API
searchInput.addEventListener('input', () => {
  clearTimeout(debounceTimer);
  const query = searchInput.value.trim();
  if (query.length < 3) {
    window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
    applyFilters();
    return;
  }
  debounceTimer = setTimeout(async () => {
    try {
      const books = await fetchBooks(query);
      window.__BOOKS_DATA__ = books;
      applyFilters();
      const allIds = books.map(b => b.google_id || String(b.id)).filter(Boolean);
      const stats = await fetchRatingStats(allIds);
      window.__BOOKS_DATA__ = window.__BOOKS_DATA__.map(b => {
        const key = b.google_id || String(b.id);
        const s = stats[key];
        if (s) {
          return {...b, rating_avg: s.rating_avg, rating_count: s.rating_count};
        }
        return b;
      });
      applyFilters(false);
    } catch (err) {
      showToast('Gagal mencari buku');
      window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
      applyFilters();
    }
  }, 400);
});

// Clear search
searchClear.addEventListener('click', () => {
  searchInput.value = '';
  window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
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
  window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
  applyFilters();
  showToast('Filter telah direset');
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

document.addEventListener('DOMContentLoaded', async () => {
  window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
  populateGenres();
  applyFilters();

  const ids = window.__FEATURED_BOOKS__.map(b => String(b.id));
  const stats = await fetchRatingStats(ids);
  window.__BOOKS_DATA__ = window.__BOOKS_DATA__.map(b => ({
    ...b,
    rating_avg: stats[String(b.id)]?.rating_avg ?? b.rating_avg,
    rating_count: stats[String(b.id)]?.rating_count ?? b.rating_count,
  }));
  applyFilters(false);
});
