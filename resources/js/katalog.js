import "./custom-select";
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
    { keys: ['adventure','action'], val: 'Petualangan' },
    { keys: ['dystopia','dystopian'], val: 'Distopia' },
    { keys: ['religion','spirituality','islam'], val: 'Religi' },
    { keys: ['science','technology'], val: 'Sains & Teknologi' },
    { keys: ['education','academic','textbook'], val: 'Edukasi' },
    { keys: ['nonfiction','non-fiction','reference','philosophy','politics','social'], val: 'Non-Fiksi' },
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

// DOM refs - will be initialized when DOM is ready
let grid, pagination, searchInput, searchClear, filterGenre, filterRating, sortSelect, resultCount, emptyState, resetBtn, activeFilters, scrollTopBtn;

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

function truncateSynopsis(text, maxLength = 150) {
  if (!text) return '';
  // Strip HTML tags since Google Books API can return HTML in descriptions
  const stripped = text.replace(/<[^>]*>/g, '');
  if (stripped.length <= maxLength) return stripped;
  return stripped.substring(0, maxLength).trimEnd() + '…';
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
  const predefinedGenres = [
    'Biografi', 'Bisnis', 'Distopia', 'Edukasi', 'Fantasi', 'Fiksi', 'Horror', 'Komik',
    'Misteri', 'Non-Fiksi', 'Pengembangan Diri', 'Petualangan', 'Puisi', 'Religi',
    'Romansa', 'Sains & Teknologi', 'Sci-Fi', 'Sejarah', 'Thriller', 'Teenlit'
  ];
  const genres = [...new Set([...predefinedGenres, ...(allBooks.flatMap(b => b.genres))])].sort();
  
  // Desktop genre select container
  const desktopGenreContainer = document.getElementById('ulasan-filter-genre-container');
  const desktopSelect = document.getElementById('ulasan-filter-genre');
  const desktopOptionsDiv = desktopGenreContainer?.querySelector('.custom-select-options');
  
  // Mobile genre select container
  const mobileGenreContainer = document.getElementById('mobile-filter-genre-container');
  const mobileSelect = document.getElementById('mobile-filter-genre');
  const mobileOptionsDiv = mobileGenreContainer?.querySelector('.custom-select-options');
  
  genres.forEach(g => {
    // Hidden Select options
    const optDesktop = document.createElement('option');
    optDesktop.value = g;
    optDesktop.textContent = g;
    if (desktopSelect) desktopSelect.appendChild(optDesktop);
    
    if (mobileSelect) {
      const optMobile = optDesktop.cloneNode(true);
      mobileSelect.appendChild(optMobile);
    }
    
    // Custom Checkbox for Desktop
    if (desktopOptionsDiv) {
        const label = document.createElement('label');
        label.className = 'custom-select-option px-4 py-2 text-sm hover:bg-gray-50 transition-colors cursor-pointer flex items-start gap-2';
        label.innerHTML = `
            <div class="mt-0.5 relative flex items-center justify-center w-4 h-4 border-2 border-gray-300 rounded focus-within:border-[#444] bg-white transition-colors">
                <input type="checkbox" name="ulasan-filter-genre[]" value="${g}" data-label="${g}" class="peer absolute inset-0 opacity-0 cursor-pointer w-full h-full m-0">
                <svg class="peer-checked:opacity-100 opacity-0 pointer-events-none text-[#444]" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <span class="flex-1 text-gray-600 peer-checked:font-bold peer-checked:text-[#444] leading-tight select-none">${g}</span>
        `;
        desktopOptionsDiv.appendChild(label);
        
        // Add event listener to new checkbox
        const cb = label.querySelector('input[type="checkbox"]');
        cb.addEventListener('change', () => {
            const container = cb.closest('.custom-select-container');
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            const selected = Array.from(checkboxes).filter(c => c.checked);
            const labelEl = container.querySelector('.custom-select-label');
            
            if (selected.length === 0) {
                labelEl.textContent = 'Semua Genre';
            } else if (selected.length === 1) {
                labelEl.textContent = selected[0].dataset.label;
            } else if (selected.length <= 2) {
                 labelEl.textContent = selected.map(c => c.dataset.label).join(', ');
            } else {
                labelEl.textContent = `${selected.length} Dipilih`;
            }
            
            Array.from(desktopSelect.options).forEach(opt => {
                opt.selected = selected.some(c => c.value === opt.value);
            });
            desktopSelect.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    // Custom Checkbox for Mobile
    if (mobileOptionsDiv) {
        const label = document.createElement('label');
        label.className = 'custom-select-option px-4 py-2 text-sm hover:bg-gray-50 transition-colors cursor-pointer flex items-start gap-2';
        label.innerHTML = `
            <div class="mt-0.5 relative flex items-center justify-center w-4 h-4 border-2 border-gray-300 rounded focus-within:border-[#444] bg-white transition-colors">
                <input type="checkbox" name="mobile-filter-genre[]" value="${g}" data-label="${g}" class="peer absolute inset-0 opacity-0 cursor-pointer w-full h-full m-0">
                <svg class="peer-checked:opacity-100 opacity-0 pointer-events-none text-[#444]" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <span class="flex-1 text-gray-600 peer-checked:font-bold peer-checked:text-[#444] leading-tight select-none">${g}</span>
        `;
        mobileOptionsDiv.appendChild(label);
        
        // Add event listener to new checkbox
        const cb = label.querySelector('input[type="checkbox"]');
        cb.addEventListener('change', () => {
            const container = cb.closest('.custom-select-container');
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            const selected = Array.from(checkboxes).filter(c => c.checked);
            const labelEl = container.querySelector('.custom-select-label');
            
            if (selected.length === 0) {
                labelEl.textContent = 'Semua Genre';
            } else if (selected.length === 1) {
                labelEl.textContent = selected[0].dataset.label;
            } else if (selected.length <= 2) {
                 labelEl.textContent = selected.map(c => c.dataset.label).join(', ');
            } else {
                labelEl.textContent = `${selected.length} Dipilih`;
            }
            
            Array.from(mobileSelect.options).forEach(opt => {
                opt.selected = selected.some(c => c.value === opt.value);
            });
            // We don't dispatch change immediately on mobile, it relies on "Terapkan" button
        });
    }
  });
}

// ══════════════════════════════════════
// FILTER + SORT + PAGINATE
// ══════════════════════════════════════

function applyFilters(resetPage = true) {
  if (resetPage) currentPage = 1;

  const allBooks = window.__BOOKS_DATA__ || [];
  const query      = searchInput.value.toLowerCase().trim();
  
  // Read selected genres (multiple)
  const selectedGenres = Array.from(filterGenre.selectedOptions).map(opt => opt.value).filter(val => val !== "");
  
  const minRating  = filterRating.value ? Number(filterRating.value) : 0;
  const sortKey    = sortSelect.value;

  // Sync with mobile controls
  const mobileGenre = document.getElementById('mobile-filter-genre');
  const mobileRating = document.getElementById('mobile-filter-rating');
  const mobileSort = document.getElementById('mobile-sort');
  
  if (mobileGenre) {
      Array.from(mobileGenre.options).forEach(opt => {
          opt.selected = selectedGenres.includes(opt.value);
      });
      // trigger event to update ui
      mobileGenre.dispatchEvent(new Event('change'));
  }
  if (mobileRating) {
      mobileRating.value = minRating ? String(minRating) : '';
      mobileRating.dispatchEvent(new Event('change'));
  }
  if (mobileSort) {
      mobileSort.value = sortKey;
      mobileSort.dispatchEvent(new Event('change'));
  }

  // Toggle clear button
  searchClear.classList.toggle('hidden', !query);

  let result = allBooks.filter(b => {
    const matchSearch = !query
      || b.judul.toLowerCase().includes(query)
      || b.penulis.toLowerCase().includes(query)
      || b.genres.some(g => g.toLowerCase().includes(query));
      
    // Match ANY of the selected genres. If none selected, allow all.
    const matchGenre = selectedGenres.length === 0 || b.genres.some(g => selectedGenres.includes(g));
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
  renderActiveFilters(query, selectedGenres, minRating);

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
// RENDER SKELETON & BOOK CARDS
// ══════════════════════════════════════

function renderSkeletons() {
  resultCount.textContent = '...';
  grid.classList.remove('hidden');
  emptyState.classList.add('hidden');
  pagination.innerHTML = '';
  
  grid.innerHTML = Array(8).fill(0).map(() => `
    <div class="card-animate bg-white border-[1.5px] border-[#e8e8e8] rounded-2xl overflow-hidden flex flex-col">
      <div class="w-full aspect-[2/3] skeleton"></div>
      <div class="p-3 md:p-5 flex flex-col flex-1 gap-2 mt-1">
        <div class="h-4 md:h-5 skeleton w-3/4 mb-1 rounded"></div>
        <div class="h-3 skeleton w-1/2 mb-3 rounded"></div>
        <div class="hidden md:block h-3 skeleton w-full mb-1 mt-auto rounded"></div>
        <div class="hidden md:block h-3 skeleton w-5/6 rounded"></div>
      </div>
    </div>
  `).join('');
}

function renderCards(books) {
  try {
  grid.innerHTML = books.map((book, i) => {
    const avg = Number(book.rating_avg) || 0;
    const count = Number(book.rating_count) || 0;
    const genres = book.genres || [];
    return `
    <article class="card-animate group bg-white border-[1.5px] border-[#e8e8e8] rounded-2xl overflow-hidden cursor-pointer
                    flex flex-col hover:border-[#444] hover:-translate-y-1 transition-all duration-300"
             style="animation-delay: ${i * 0.06}s"
             data-book-id="${book.id}"
             data-google-id="${book.google_id || ''}">
      
      <!-- Cover -->
      <div class="relative aspect-[2/3] overflow-hidden bg-gray-100">
        ${book.cover
          ? `<img src="${book.cover}" alt="Sampul ${book.judul}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />`
          : `<div class="w-full h-full flex items-center justify-center text-3xl font-black text-white/20 group-hover:scale-105 transition-transform duration-500"
                  style="background: linear-gradient(135deg, ${book.gradient_from || '#C7E7FF'}, ${book.gradient_to || '#FFDDAF'})">
                ${book.judul.charAt(0)}
             </div>`
        }
        <!-- Rating Badge on Cover -->
        <div class="md:hidden absolute top-2 left-2 z-10 flex items-center gap-1 px-2 py-1 rounded-lg bg-black/60 backdrop-blur-md text-white text-[0.68rem] font-bold">
          <span class="text-[#F5C518]">★</span>
          <span>${avg > 0 ? avg.toFixed(1) : '-'}</span>
        </div>
        <!-- Hover overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden md:flex items-end p-4">
          <span class="text-white text-xs font-semibold bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full">
            Lihat Detail →
          </span>
        </div>
      </div>

      <!-- Info -->
      <div class="p-3 md:p-5 flex flex-col flex-1">
        <h3 class="text-xs md:text-base font-bold text-text leading-tight mb-0.5 md:mb-1 line-clamp-2">${book.judul}</h3>
        <p class="text-[0.68rem] md:text-[0.78rem] text-text/50 mb-2 line-clamp-1">${book.penulis}</p>

        <!-- Rating (desktop only) -->
        <div class="hidden md:flex items-center gap-1.5 mb-3">
          <div class="flex gap-0.5">${starsHtml(avg)}</div>
          <span class="text-[0.75rem] font-bold text-text">${avg > 0 ? avg.toFixed(1) : '0'}</span>
          <span class="text-[0.7rem] text-text/35">(${count} Ulasan)</span>
        </div>
        <!-- Rating (mobile only) -->
        <div class="flex md:hidden items-center gap-1 mb-2">
          <span class="text-[0.65rem] text-text/40">(${count} Ulasan)</span>
        </div>

        <!-- Synopsis (desktop only) -->
        <p class="hidden md:block text-[0.78rem] text-text/55 leading-relaxed mb-4 flex-1 line-clamp-3">${truncateSynopsis(book.sinopsis, 150)}</p>

        <!-- Genre pills (desktop only) -->
        <div class="hidden md:flex gap-1.5 flex-wrap mb-4">
          ${genres.map(g => `
            <span class="px-3 py-1 text-[0.68rem] font-medium text-text/70 border border-[#e0e0e0] rounded-full">${g}</span>
          `).join('')}
        </div>

        <!-- CTA (desktop only) -->
        <a href="/detail-buku/${book.id || book.google_id}" 
           class="hidden md:inline-flex items-center gap-2 px-5 py-2 text-[0.8rem] font-bold text-text bg-[#FFDDAF] rounded-full border-[1.5px] border-text
                  hover:bg-amber-300 hover:-translate-y-px transition-all duration-200 self-start"
           onclick="event.stopPropagation()">
          Lihat Ulasan
        </a>
      </div>
    </article>
  `;
  }).join('');
  } catch(err) {
    console.error('renderCards error:', err);
  }

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

function renderActiveFilters(query, genres, minRating) {
  const chips = [];

  if (query) {
    chips.push(`
      <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#FFDDAF]/40 border border-[#FFDDAF] rounded-full hover:bg-[#FFDDAF] transition-colors"
              data-clear="search">
        🔍 "${query}" <span class="text-text/40">×</span>
      </button>
    `);
  }
  if (genres && genres.length > 0) {
    genres.forEach(g => {
      chips.push(`
        <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#C7E7FF]/40 border border-[#C7E7FF] rounded-full hover:bg-[#C7E7FF] transition-colors"
                data-clear="genre" data-genre-value="${g}">
          📂 ${g} <span class="text-text/40">×</span>
        </button>
      `);
    });
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
      if (type === 'search') { searchInput.value = ''; }
      if (type === 'genre') {
        const genreVal = btn.dataset.genreValue;
        const container = document.getElementById('ulasan-filter-genre-container');
        if (container) {
          const cb = container.querySelector(`input[type="checkbox"][value="${genreVal}"]`);
          if (cb) { cb.checked = false; cb.dispatchEvent(new Event('change', { bubbles: true })); return; }
        }
        filterGenre.value = '';
      }
      if (type === 'rating') { filterRating.value = ''; filterRating.dispatchEvent(new Event('change')); }
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
// INIT — all DOM-dependent code runs here
// ══════════════════════════════════════

function initKatalog() {
  // DOM REFS
  grid           = document.getElementById('ulasan-grid');
  pagination     = document.getElementById('ulasan-pagination');
  searchInput    = document.getElementById('ulasan-search-input');
  searchClear    = document.getElementById('ulasan-search-clear');
  filterGenre    = document.getElementById('ulasan-filter-genre');
  filterRating   = document.getElementById('ulasan-filter-rating');
  sortSelect     = document.getElementById('ulasan-sort');
  resultCount    = document.getElementById('ulasan-result-count');
  emptyState     = document.getElementById('ulasan-empty');
  resetBtn       = document.getElementById('ulasan-reset-filters');
  activeFilters  = document.getElementById('ulasan-active-filters');
  scrollTopBtn   = document.getElementById('scrollTopBtn');

  // Guard: if we're not on the katalog page, bail out
  if (!grid || !searchInput) return;

  // EVENT LISTENERS

  // Debounced search — fetches from Google Books API
  searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const query = searchInput.value.trim();
    if (query.length < 3) {
      window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
      applyFilters();
      return;
    }
    
    // Show loading skeleton while typing/waiting for debounce
    renderSkeletons();
    
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

  // Mobile Filter Dialog Event Listeners
  const mobileFilterDialog = document.getElementById('mobile-filter-dialog');
  const mobileFilterBtn = document.getElementById('ulasan-mobile-filter-btn');
  const mobileFilterClose = document.getElementById('close-filter-dialog');
  const mobileFilterReset = document.getElementById('mobile-filter-reset');

  if (mobileFilterBtn && mobileFilterDialog) {
    mobileFilterBtn.addEventListener('click', () => {
      mobileFilterDialog.showModal();
    });
  }

  if (mobileFilterClose && mobileFilterDialog) {
    mobileFilterClose.addEventListener('click', () => {
      mobileFilterDialog.close();
    });
  }

  if (mobileFilterDialog) {
    // Light dismiss on backdrop click
    mobileFilterDialog.addEventListener('click', (e) => {
      const rect = mobileFilterDialog.getBoundingClientRect();
      const isInDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
      if (!isInDialog) {
        mobileFilterDialog.close();
      }
    });

    // Handle Form Submission within Dialog
    const form = mobileFilterDialog.querySelector('form');
    if (form) {
      form.addEventListener('submit', (e) => {
        filterGenre.value = document.getElementById('mobile-filter-genre').value;
        filterRating.value = document.getElementById('mobile-filter-rating').value;
        sortSelect.value = document.getElementById('mobile-sort').value;
        applyFilters();
      });
    }
  }

  if (mobileFilterReset && mobileFilterDialog) {
    mobileFilterReset.addEventListener('click', () => {
      searchInput.value = '';
      filterGenre.value = '';
      filterRating.value = '';
      sortSelect.value = 'rating-desc';
      window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
      applyFilters();
      mobileFilterDialog.close();
      showToast('Filter telah direset');
    });
  }

  // Load data and render
  window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
  populateGenres();
  applyFilters();

  // Fetch live rating stats
  const ids = (window.__FEATURED_BOOKS__ || []).map(b => String(b.id));
  fetchRatingStats(ids).then(stats => {
    window.__BOOKS_DATA__ = window.__BOOKS_DATA__.map(b => ({
      ...b,
      rating_avg: stats[String(b.id)]?.rating_avg ?? b.rating_avg,
      rating_count: stats[String(b.id)]?.rating_count ?? b.rating_count,
    }));
    applyFilters(false);
  });
}

// Run when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initKatalog);
} else {
  initKatalog();
}
