import { $, getInitial } from './utils.js';

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

function fixCoverUrl(url) {
  if (!url) return '';
  return url
    .replace(/^http:\/\//i, 'https://')
    .replace('zoom=1', 'zoom=2')
    .replace('&edge=curl', '');
}

function extractISBN(info) {
  if (!info.industryIdentifiers) return '';
  const isbn13 = info.industryIdentifiers.find(id => id.type === 'ISBN_13');
  const isbn10 = info.industryIdentifiers.find(id => id.type === 'ISBN_10');
  return isbn13?.identifier || isbn10?.identifier || '';
}

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

function searchLocalBooks(query) {
  const local = window.__FEATURED_BOOKS__ || [];
  const q = query.toLowerCase();
  return local
    .filter(b => b.judul.toLowerCase().includes(q) || b.penulis.toLowerCase().includes(q))
    .map(b => ({
      judul: b.judul,
      penulis: b.penulis,
      tahun: b.tahun || '',
      kategori: b.kategori || '',
      halaman: b.jumlah_halaman || '',
      isbn: b.isbn || '',
      coverUrl: b.cover || '',
      source: 'local',
    }));
}

async function fetchOpenLibrary(query) {
  const url = `https://openlibrary.org/search.json?q=${encodeURIComponent(query)}&limit=5`;
  const res = await fetch(url);
  if (!res.ok) throw new Error(`Open Library error: ${res.status}`);
  const data = await res.json();
  return (data.docs || []).map(b => ({
    judul: b.title,
    penulis: b.author_name?.join(', ') || '',
    tahun: b.first_publish_year || '',
    kategori: b.subject ? mapCategory(b.subject.slice(0, 3)) : 'Fiksi',
    halaman: '',
    deskripsi: b.description || b.subtitle || '',
    isbn: b.isbn?.[0] || '',
    coverUrl: b.cover_i ? `https://covers.openlibrary.org/b/id/${b.cover_i}-L.jpg` : '',
    source: 'openlibrary',
  }));
}

let searchDebounceTimer = null;
let currentSearchAbort = null;

export function initBookSearch() {
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

  document.addEventListener('click', (e) => {
    const wrapper = $('#book-search-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
      hideSearchResults();
    }
  });
}

function buildSearchQuery(raw) {
  const q = raw.trim();
  const isbnClean = q.replace(/[-\s]/g, '');
  if (/^\d{10,13}$/.test(isbnClean)) return `isbn:${isbnClean}`;
  const wordCount = q.split(/\s+/).length;
  if (wordCount <= 2) return `intitle:${q}`;
  return q;
}

async function searchBooks(query, retryCount = 0) {
  if (currentSearchAbort) currentSearchAbort.abort();
  currentSearchAbort = new AbortController();

  showSearchSpinner(true);
  const localResults = searchLocalBooks(query);
  let apiResults = [];

  try {
    const smartQuery = buildSearchQuery(query);
    const keyParam = GOOGLE_BOOKS_KEY ? `&key=${GOOGLE_BOOKS_KEY}` : '';
    const url = `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(smartQuery)}&maxResults=8&printType=books&orderBy=relevance${keyParam}`;

    const response = await fetch(url, { signal: currentSearchAbort.signal, headers: {'Accept': 'application/json'} });

    if (response.ok) {
      const data = await response.json();
      if (data.totalItems && data.items?.length) {
        apiResults = data.items.map(parseBookVolume);
      }
    }

    if (apiResults.length == 0 && retryCount == 0) {
      const wordCount = query.trim().split(/\s+/).length;
      const altQuery = wordCount <= 2 ? query.trim() : `intitle:${query.trim()}`;
      const fallbackUrl = `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(altQuery)}&maxResults=8&printType=books&orderBy=relevance${keyParam}`;
      const fbResp = await fetch(fallbackUrl, { signal: currentSearchAbort.signal });
      if (fbResp.ok) {
        const fbData = await fbResp.json();
        if (fbData.totalItems && fbData.items?.length) {
          apiResults = fbData.items.map(parseBookVolume);
        }
      }
    }
  } catch (err) {
    if (err.name == 'AbortError') {
      showSearchSpinner(false);
      return;
    }
    console.warn('[Alinea] Google Books error:', err.message);
  }

  if (apiResults.length == 0) {
    try {
      apiResults = await fetchOpenLibrary(query);
    } catch (e) {
      console.warn('[Alinea] Open Library failed');
    }
  }

  const seen = new Set(localResults.map(b => b.judul.toLowerCase()));
  const merged = [...localResults];
  for (const b of apiResults) {
    if (!seen.has(b.judul.toLowerCase())) {
      seen.add(b.judul.toLowerCase());
      merged.push(b);
    }
  }

  showSearchResults(merged);
  showSearchSpinner(false);
}

function showSearchSpinner(show) {
  const spinner = $('#book-search-spinner');
  if (spinner) spinner.classList.toggle('hidden', !show);
}

function showSearchResults(books) {
  const container = $('#book-search-results');
  if (!container) return;

  if (books === null) {
    container.innerHTML = `
      <div class="p-4 text-center">
        <p class="text-xs text-red-400 font-medium">Gagal mencari buku.</p>
        <p class="text-[11px] text-gray-300 mt-1">Kuota API mungkin habis. Coba lagi nanti atau isi manual di bawah.</p>
      </div>`;
    container.classList.remove('hidden');
    return;
  }

  if (books.length === 0) {
    container.innerHTML = `
      <div class="p-4 text-center">
        <p class="text-3xl mb-1.5"></p>
        <p class="text-xs text-gray-400 font-medium">Tidak ada hasil ditemukan.</p>
        <p class="text-[11px] text-gray-300 mt-0.5">Coba kata kunci lain, atau isi manual di bawah.</p>
      </div>`;
    container.classList.remove('hidden');
    return;
  }

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

  container.querySelectorAll('[data-search-result]').forEach(btn => {
    btn.addEventListener('click', () => {
      const idx = parseInt(btn.dataset.searchResult);
      selectSearchResult(books[idx]);
    });
  });
}

export function hideSearchResults() {
  const container = $('#book-search-results');
  if (container) { container.classList.add('hidden'); container.innerHTML = ''; }
}

function selectSearchResult(book) {
  hideSearchResults();
  $('#book-search-input').value = '';

  fillField('add-book-judul', book.judul);
  fillField('add-book-penulis', book.penulis);
  if (book.tahun) fillField('add-book-tahun', book.tahun);
  if (book.halaman) fillField('add-book-halaman', book.halaman);
  if (book.isbn) fillField('add-book-isbn-manual', book.isbn);
  selectOption('add-book-kategori', book.kategori);
  $('#add-book-cover-url').value = book.coverUrl;

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

  const titleEl = $('#preview-title');
  if (titleEl) titleEl.textContent = book.judul;
  const authorEl = $('#preview-author');
  if (authorEl) authorEl.textContent = book.penulis;
  const yearEl = $('#preview-year');
  if (yearEl) yearEl.textContent = book.tahun ? `${book.tahun}` : '';
  const catEl = $('#preview-category');
  if (catEl) catEl.textContent = `${book.kategori}`;

  const pagesEl = $('#preview-pages');
  if (pagesEl) {
    if (book.halaman) { pagesEl.textContent = `${book.halaman} hal`; pagesEl.classList.remove('hidden'); }
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

export function hideBookPreview() {
  const preview = $('#book-preview');
  if (preview) preview.classList.add('hidden');
}

export function clearBookSelection() {
  hideBookPreview();
  $('#add-book-form')?.reset();
  $('#add-book-cover-url').value = '';
  $('#book-search-input').value = '';
}
