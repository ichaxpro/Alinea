const IS_AUTHENTICATED = document.querySelector('meta[name="user-auth"]')?.content === 'true';
const GOOGLE_BOOKS_API = 'https://www.googleapis.com/books/v1/volumes';
const GOOGLE_BOOKS_KEY = document.querySelector('meta[name="google-books-key"]')?.content || '';

function fixCoverUrl(url) {
    if (!url) {
        return '';
    }
    return url.replace(/^http:\/\//i, 'https://')
              .replace('zoom=1', 'zoom=2')
              .replace('&edge=curl', '');
}

function mapCategory(categories) {
    if (!categories || !categories.length) {
        return 'Fiksi';
    }
    const cat = categories.join(' ').toLowerCase();
    const mapping = [
        {keys: ['thriller','suspense','crime'], val: 'Thriller'},
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
        if (m.keys.some(k => cat.includes(k))) {
            return m.val;
        }
    }
    return 'Fiksi';
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
        rating_avg: info.averageRating || 0,
        rating_count: info.ratingsCount || 0,
        sinopsis: info.description || '',
        genres: [mapCategory(info.categories)],
        cover: coverUrl || null,
        gradient_from: '#C7E7FF',
        gradient_to: '#FFDDAF',
        google_id: volume.id,
    };
}

async function fetchGoogleBooks(query, maxResults = 5) {
    const keyParam = GOOGLE_BOOKS_KEY ? `&key=${GOOGLE_BOOKS_KEY}` : '';
    const url = `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&maxResults=${maxResults}&printType=books&orderBy=relevance${keyParam}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error(`API error: ${res.status}`);
    const data = await res.json();
    return (data.items || []).map(v => parseBookVolume(v));
}

function buildOverlay() {
    const overlay = document.createElement('div');
    overlay.id = 'global-search-overlay';
    // Note: plain JS comments used here — this is a .js file, not a Blade template
    overlay.innerHTML = `
    <div id="global-search-backdrop" class="fixed inset-0 z-[999] bg-black/40 backdrop-blur-sm"></div>
        <div id="global-search-wrapper" class="fixed inset-0 z-[1000] flex items-start justify-center pt-[15vh] px-4">
            <div id="global-search-panel" class="w-full max-w-xl bg-white border-[1.5px] border-[#444] rounded-2xl shadow-2xl overflow-hidden opacity-0 scale-95 transition-all duration-200">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-200">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input id="global-search-input" type="text" placeholder="Cari pengguna, buku, atau klub..."
                           class="flex-1 border-none outline-none bg-transparent text-base placeholder-gray-300" autocomplete="off" />
                    <button id="global-search-close" class="text-gray-400 hover:text-[#444] transition-colors p-1" aria-label="Tutup pencarian">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                <div id="global-search-results" class="max-h-[60vh] overflow-y-auto p-2">
                    <div id="global-search-empty" class="hidden text-center py-10 text-gray-400 text-sm">Mulai mengetik untuk mencari...</div>
                    <div id="global-search-loading" class="hidden text-center py-10 text-gray-400 text-sm">Mencari...</div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    return {
        overlay,
        backdrop: document.getElementById('global-search-backdrop'),
        wrapper: document.getElementById('global-search-wrapper'),
        panel: document.getElementById('global-search-panel'),
        input: document.getElementById('global-search-input'),
        results: document.getElementById('global-search-results'),
        empty: document.getElementById('global-search-empty'),
        loading: document.getElementById('global-search-loading'),
        closeBtn: document.getElementById('global-search-close'),
    };
}

let dom = null;
let debounceTimer = null;
let currentAbort = null;
let selectedIndex = -1;
let currentResults = [];

function openSearch() {
    if (!dom) dom = buildOverlay();
    dom.overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        dom.panel.classList.remove('opacity-0', 'scale-95');
        dom.panel.classList.add('opacity-100', 'scale-100');
        dom.input.focus();
    });
}

function closeSearch() {
    if (!dom) {
        return;
    }
    dom.panel.classList.remove('opacity-100', 'scale-100');
    dom.panel.classList.add('opacity-0', 'scale-95');
    setTimeout(() => {
        dom.overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
    dom.input.value = '';
    // Restore initial state with hidden class so it shows correctly on next open
    dom.results.innerHTML = `<div id="global-search-empty" class="hidden text-center py-10 text-gray-400 text-sm">Mulai mengetik untuk mencari...</div>`;
    currentResults = [];
    selectedIndex = -1;
}

function showLoading() {
    dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Mencari...</div>`;
}

function showEmpty(query) {
    dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Tidak ada hasil untuk "<strong>${escapeHtml(query)}</strong>"</div>`;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

async function performSearch(query) {
    if (query.length < 2) {
        dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Minimal 2 karakter...</div>`;
        currentResults = [];
        return;
    }

    if (currentAbort) {
        currentAbort.abort();
    }
    const abortController = new AbortController();
    currentAbort = abortController;

    showLoading();

    try {
        const [apiRes, googleBooks] = await Promise.all([
            fetch(`/api/search?q=${encodeURIComponent(query)}`, {
                signal: abortController.signal,
                headers: {'Accept': 'application/json'},
                credentials: 'same-origin',
            }),
            fetchGoogleBooks(query, 5).catch(() => []),
        ]);

        if (abortController.signal.aborted) {
            return;
        }

        const apiData = apiRes.ok ? await apiRes.json() : { users: [], clubs: [], books: []};

        const localTitles = new Set((apiData.books || []).map(b => b.judul.toLowerCase()));
        const mergedBooks = [...(apiData.books || [])];
        for (const gb of googleBooks) {
            if (!localTitles.has(gb.judul.toLowerCase())) {
                mergedBooks.push({
                    // id is null — this book only exists in Google Books, not locally
                    id: null,
                    google_id: gb.google_id,
                    judul: gb.judul,
                    penulis: gb.penulis,
                    cover_url: gb.cover,
                    isbn: '',
                    gradient_from: gb.gradient_from,
                    gradient_to: gb.gradient_to,
                });
                localTitles.add(gb.judul.toLowerCase());
            }
        }

        const totalResults = (apiData.users?.length || 0) + (apiData.clubs?.length || 0) + mergedBooks.length;

        if (totalResults === 0) {
            showEmpty(query);
            currentResults = [];
            return;
        }

        renderResults(apiData.users || [], apiData.clubs || [], mergedBooks);
    } catch (err) {
        if (err.name === 'AbortError') {
            return;
        }
        dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Terjadi kesalahan. Coba lagi.</div>`;
    }
}

function renderResults(users, clubs, books) {
    let html = '';
    currentResults = [];

    if (users.length) {
        html += `<div class="px-3 pt-3 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Pengguna</div>`;
        users.forEach((u) => {
            const idx = currentResults.length;
            html += `
                <button data-result-idx="${idx}" data-url="/user/${u.id}"
                        class="result-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors hover:bg-gray-50 focus:bg-gray-50 outline-none">
                    <div class="w-9 h-9 rounded-full border-[1.5px] border-[#444] flex-shrink-0 overflow-hidden
                                bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center">
                        ${u.avatar_url
                            ? `<img src="${u.avatar_url}" alt="" class="w-full h-full object-cover">`
                            : `<span class="text-sm font-black text-text/60">${u.initial}</span>`
                        }
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold truncate">${escapeHtml(u.name)}</div>
                        ${u.username ? `<div class="text-xs text-gray-400 truncate">@${escapeHtml(u.username)}</div>` : ''}
                    </div>
                </button>
            `;
            // Bug fix: use a plain forward-slash URL, not an escaped one
            currentResults.push({type: 'user', url: `/user/${u.id}`});
        });
    }

    if (clubs.length) {
        html += `<div class="px-3 pt-4 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Klub</div>`;
        clubs.forEach((c) => {
            const idx = currentResults.length;
            const coverStyle = c.foto_klub ? `background-image: url('${c.foto_klub}'); background-size: cover; background-position: center;`
                : `background: linear-gradient(135deg, ${c.gradient_from}, ${c.gradient_to})`;
            html += `
                <button data-result-idx="${idx}" data-url="/klub?highlight=${c.id}"
                        class="result-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors hover:bg-gray-50 focus:bg-gray-50 outline-none">
                    <div class="w-9 h-9 rounded-lg border-[1.5px] border-[#444] flex-shrink-0" style="${coverStyle}"></div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold truncate">${escapeHtml(c.nama_klub)}</div>
                        <div class="text-xs text-gray-400">
                            <span>${escapeHtml(c.kategori)}</span>
                            <span class="mx-1">·</span>
                            <span>${c.member_count} member</span>
                        </div>
                    </div>
                </button>
            `;
            currentResults.push({type: 'club', url: `/klub?highlight=${c.id}`});
        });
    }

    if (books.length) {
        html += `<div class="px-3 pt-4 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Buku</div>`;
        books.forEach((b) => {
            const idx = currentResults.length;
            // Bug fix: prefer local id when available; fall back to google_id for Google-only results
            const detailParam = b.id ?? b.google_id;
            const coverStyle = b.cover_url
                ? `background-image: url('${b.cover_url}'); background-size: cover; background-position: center;`
                : `background: linear-gradient(135deg, ${b.gradient_from}, ${b.gradient_to})`;
            html += `
                <button data-result-idx="${idx}" data-url="/detail-buku/${detailParam}"
                        class="result-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors hover:bg-gray-50 focus:bg-gray-50 outline-none">
                    <div class="w-9 h-12 rounded-lg border-[1.5px] border-[#444] flex-shrink-0 bg-cover bg-center" style="${coverStyle}"></div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold truncate">${escapeHtml(b.judul)}</div>
                        <div class="text-xs text-gray-400 truncate">${escapeHtml(b.penulis)}</div>
                    </div>
                </button>
            `;
            currentResults.push({ type: 'book', url: `/detail-buku/${detailParam}` });
        });
    }

    dom.results.innerHTML = html;

    dom.results.querySelectorAll('.result-item').forEach(el => {
        el.addEventListener('click', () => {
            const idx = parseInt(el.dataset.resultIdx);
            navigateToResult(idx);
        });
    });
}

function navigateToResult(idx) {
    if (idx < 0 || idx >= currentResults.length) {
        return;
    }
    const result = currentResults[idx];
    closeSearch();
    window.location.href = result.url;
}

// Build overlay lazily — only once the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    dom = buildOverlay();
    dom.overlay.classList.add('hidden');

    document.getElementById('navbar-search-btn')?.addEventListener('click', (e) => {
        e.preventDefault();
        openSearch();
    });

    dom.backdrop.addEventListener('click', closeSearch);
    dom.closeBtn.addEventListener('click', closeSearch);

    // Close when clicking the dimmed area outside the panel
    dom.wrapper.addEventListener('click', closeSearch);
    dom.panel.addEventListener('click', (e) => e.stopPropagation());

    dom.input.addEventListener('input', () => {
        if (!IS_AUTHENTICATED) {
            dom.results.innerHTML = `
            <div class="text-center py-10 text-gray-400 text-sm">
                    Silakan <a href="/login" class="text-[#5DA9FF] font-semibold underline">masuk</a> terlebih dahulu untuk mencari.
                </div>
            `;
            return;
        }
        clearTimeout(debounceTimer);
        selectedIndex = -1;
        const query = dom.input.value.trim();
        if (query.length < 2) {
            dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Minimal 2 karakter...</div>`;
            currentResults = [];
            return;
        }
        debounceTimer = setTimeout(() => performSearch(query), 300);
    });

    dom.input.addEventListener('keydown', (e) => {
        const items = dom.results.querySelectorAll('.result-item');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, currentResults.length - 1);
            updateSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            updateSelection(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0) {
                navigateToResult(selectedIndex);
            }
        } else if (e.key === 'Escape') {
            closeSearch();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && dom && !dom.overlay.classList.contains('hidden')) {
            closeSearch();
        }
    });
});

function updateSelection(items) {
    items.forEach((el, i) => {
        if (i === selectedIndex) {
            el.classList.add('bg-gray-100');
            el.scrollIntoView({ block: 'nearest' });
        } else {
            el.classList.remove('bg-gray-100');
        }
    });
    // When selectedIndex is -1 (back to input), return focus to the input
    if (selectedIndex === -1 && dom) {
        dom.input.focus();
    }
}