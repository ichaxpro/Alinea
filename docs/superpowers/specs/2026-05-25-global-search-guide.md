# Global Search — Implementation Guide

## Overview

Add a spotlight-style global search to the navbar search icon. Searches users, books (local DB + Google Books API), and clubs. Results grouped by type. Guests see a "login to search" prompt.

---

## Step 1: Backend API — `/api/search`

### 1a. Add route in `routes/api.php`

**⚠️ Laravel 13 difference:** `vite.config.js` has an explicit `input` array — see Step 7 below.

Inside the existing `web` + `auth` middleware group (around line 31), add a new route **before** the existing `/api/users` route:

```php
// Global search
Route::get('/search', function (Illuminate\Http\Request $request) {
    $q = $request->get('q', '');

    if (strlen(trim($q)) < 2) {
        return response()->json(['users' => [], 'clubs' => [], 'books' => []]);
    }

    $users = \App\Models\User::where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('username', 'like', "%{$q}%");
        })
        ->where('id', '!=', \Illuminate\Support\Facades\Auth::id())
        ->select('id', 'name', 'username', 'foto_profil')
        ->limit(5)
        ->get()
        ->map(fn($u) => [
            'id'         => $u->id,
            'name'       => $u->name,
            'username'   => $u->username ?? '',
            'avatar_url' => $u->avatar_url,
            'initial'    => strtoupper(substr($u->name, 0, 1)),
        ]);

    // ⚠️ BookClub model has no `members` relationship — use raw DB instead of withCount
    $clubs = \App\Models\BookClub::where('nama_klub', 'like', "%{$q}%")
        ->limit(5)
        ->get()
        ->map(function($c) {
            $memberCount = 0;
            if (\Illuminate\Support\Facades\Schema::hasTable('klub_member')) {
                $memberCount = \Illuminate\Support\Facades\DB::table('klub_member')
                    ->where('id_klub', $c->id)
                    ->count();
            }
            return [
                'id'            => $c->id,
                'nama_klub'     => $c->nama_klub,
                'kategori'      => $c->kategori,
                'foto_klub'     => $c->foto_klub ? asset('storage/' . $c->foto_klub) : null,
                'gradient_from' => $c->gradient_from ?? '#FFDDAF',
                'gradient_to'   => $c->gradient_to ?? '#C7E7FF',
                'member_count'  => $memberCount,
            ];
        });

    $books = \App\Models\FeaturedBook::where(function ($query) use ($q) {
            $query->where('judul', 'like', "%{$q}%")
                  ->orWhere('penulis', 'like', "%{$q}%");
        })
        ->select('id', 'judul', 'penulis', 'cover_url', 'isbn', 'gradient_from', 'gradient_to')
        ->limit(5)
        ->get()
        ->map(fn($b) => [
            'id'            => $b->id,
            'judul'         => $b->judul,
            'penulis'       => $b->penulis,
            'cover_url'     => $b->cover_url,
            'isbn'          => $b->isbn ?? '',
            'gradient_from' => $b->gradient_from ?? '#C7E7FF',
            'gradient_to'   => $b->gradient_to ?? '#FFDDAF',
        ]);

    return response()->json([
        'users' => $users,
        'clubs' => $clubs,
        'books' => $books,
    ]);
});
```

**Important note:**
- The route must be inside `Route::middleware(['web', 'auth'])->group(function () { ... })` block — authenticated users only
- `BookClub` model uses table `klub` and has no Eloquent relationship for members — member counts are done via raw `DB::table('klub_member')` queries (same pattern used in `KlubController`)

### 1b. Test the API

After adding the route, verify it works:
```bash
# Log in first, then:
curl -b cookies.txt http://alinea.test/api/search?q=harry
```

Or just visit `/api/search?q=test` in the browser while logged in.

---

## Step 2: New Route — User Profile Page (`/user/{id}`)

### 2a. Add route in `routes/web.php`

Add this **before** the auth middleware group (around line 58, after `/detail-buku`):

```php
Route::get('/user/{id}', function ($id) {
    $user = \App\Models\User::findOrFail($id);
    return view('user_profile', compact('user'));
})->name('user_profile');
```

### 2b. Create view `resources/views/user_profile.blade.php`

Create a minimal public user profile. Copy the structure from `resources/views/timeline_profile.blade.php` but keep it simple:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — {{ $user->name }}</title>
    <meta name="description" content="Profil {{ $user->name }} di Alinea" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">
    <x-navbar></x-navbar>

    <div class="min-h-screen pt-14">
        <div class="max-w-2xl mx-auto px-4 py-8">
            <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-8">
                <div class="flex items-center gap-6">
                    {{-- Avatar --}}
                    <div class="w-24 h-24 rounded-full border-2 border-[#444] flex-shrink-0 overflow-hidden
                                bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF]
                                flex items-center justify-center">
                        @if($user->foto_profil)
                            <img src="{{ Storage::disk('public')->url($user->foto_profil) }}"
                                 alt="Avatar" class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl font-black text-text/60">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div>
                        <h1 class="text-2xl font-bold text-[#222]">{{ $user->name }}</h1>
                        <p class="text-sm text-gray-500">@{{ $user->username ?? 'tanpa_username' }}</p>
                        @if($user->kota)
                            <p class="text-sm text-gray-400 mt-1">📍 {{ $user->kota }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

---

## Step 3: Frontend — `resources/js/global-search.js`

Create this file. It's the heart of the feature.

### Plan for the file:

1. **DOM refs** — overlay container, search input, results container
2. **Google Books helpers** — `fetchGoogleBooks(q)`, `parseBookVolume(v)`, `fixCoverUrl(url)` — copied from `katalog.js` since they're not exposed globally
3. **State** — debounce timer, selected index for keyboard nav, abort controller for cancelling stale requests
4. **Functions:**
   - `openSearch()` — show overlay, focus input
   - `closeSearch()` — hide overlay
   - `performSearch(query)` — debounced, fires API + Google Books in parallel, renders results
   - `renderResults(apiData, googleBooks)` — merges and renders grouped HTML
   - Keyboard handler (ArrowUp, ArrowDown, Enter, Escape)
5. **Event listeners** — on `#navbar-search-btn`, on close button/backdrop, on input

### Detailed code:

```javascript
// resources/js/global-search.js

const GOOGLE_BOOKS_API = 'https://www.googleapis.com/books/v1/volumes';
const GOOGLE_BOOKS_KEY = document.querySelector('meta[name="google-books-key"]')?.content || '';
// ⚠️ The `google-books-key` meta tag currently only exists on dashboard & katalog pages.
//    Add it to navbar.blade.php too (see step 4) for global search to use the API key on all pages.

// ── Helpers (from katalog.js) ──

function fixCoverUrl(url) {
    if (!url) return '';
    return url.replace(/^http:\/\//i, 'https://')
              .replace('zoom=1', 'zoom=2')
              .replace('&edge=curl', '');
}

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

// ── DOM ──

function buildOverlay() {
    const overlay = document.createElement('div');
    overlay.id = 'global-search-overlay';
    overlay.innerHTML = `
        <div id="global-search-backdrop" class="fixed inset-0 z-[999] bg-black/40 backdrop-blur-sm"></div>
        <div class="fixed inset-0 z-[1000] flex items-start justify-center pt-[15vh] px-4">
            <div id="global-search-panel" class="w-full max-w-xl bg-white border-[1.5px] border-[#444] rounded-2xl shadow-2xl overflow-hidden opacity-0 scale-95 transition-all duration-200">
                {{-- Search input --}}
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
                {{-- Results --}}
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
        panel: document.getElementById('global-search-panel'),
        input: document.getElementById('global-search-input'),
        results: document.getElementById('global-search-results'),
        empty: document.getElementById('global-search-empty'),
        loading: document.getElementById('global-search-loading'),
        closeBtn: document.getElementById('global-search-close'),
    };
}

// ── State ──
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
    if (!dom) return;
    dom.panel.classList.remove('opacity-100', 'scale-100');
    dom.panel.classList.add('opacity-0', 'scale-95');
    setTimeout(() => {
        dom.overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
    dom.input.value = '';
    dom.results.innerHTML = `<div id="global-search-empty" class="text-center py-10 text-gray-400 text-sm">Mulai mengetik untuk mencari...</div>`;
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

// ── Search ──

async function performSearch(query) {
    if (query.length < 2) {
        dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Minimal 2 karakter...</div>`;
        currentResults = [];
        return;
    }

    // Cancel previous in-flight request
    if (currentAbort) currentAbort.abort();
    const abortController = new AbortController();
    currentAbort = abortController;

    showLoading();

    try {
        const [apiRes, googleBooks] = await Promise.all([
            fetch(`/api/search?q=${encodeURIComponent(query)}`, {
                signal: abortController.signal,
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            }),
            fetchGoogleBooks(query, 5).catch(() => []),
        ]);

        if (abortController.signal.aborted) return; // stale response

        const apiData = apiRes.ok ? await apiRes.json() : { users: [], clubs: [], books: [] };

        // Merge local books + google books (dedup by title)
        const localTitles = new Set((apiData.books || []).map(b => b.judul.toLowerCase()));
        const mergedBooks = [...(apiData.books || [])];
        for (const gb of googleBooks) {
            if (!localTitles.has(gb.judul.toLowerCase())) {
                mergedBooks.push({
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
        if (err.name === 'AbortError') return;
        dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Terjadi kesalahan. Coba lagi.</div>`;
    }
}

function renderResults(users, clubs, books) {
    let html = '';
    currentResults = [];

    // Users
    if (users.length) {
        html += `<div class="px-3 pt-3 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Pengguna</div>`;
        users.forEach((u, i) => {
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
            currentResults.push({ type: 'user', url: `/user/${u.id}` });
        });
    }

    // Clubs
    if (clubs.length) {
        html += `<div class="px-3 pt-4 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Klub</div>`;
        clubs.forEach((c) => {
            const idx = currentResults.length;
            const coverStyle = c.foto_klub
                ? `background-image: url('${c.foto_klub}'); background-size: cover; background-position: center;`
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
            currentResults.push({ type: 'club', url: `/klub?highlight=${c.id}` });
        });
    }

    // Books
    if (books.length) {
        html += `<div class="px-3 pt-4 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Buku</div>`;
        books.forEach((b) => {
            const idx = currentResults.length;
            const detailParam = b.google_id || b.id;
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

    // Click handlers on result items
    dom.results.querySelectorAll('.result-item').forEach(el => {
        el.addEventListener('click', () => {
            const idx = parseInt(el.dataset.resultIdx);
            navigateToResult(idx);
        });
    });
}

function navigateToResult(idx) {
    if (idx < 0 || idx >= currentResults.length) return;
    const result = currentResults[idx];
    closeSearch();
    window.location.href = result.url;
}

// ── Keyboard navigation ──

dom = buildOverlay();
dom.overlay.classList.add('hidden'); // hidden by default

document.getElementById('navbar-search-btn')?.addEventListener('click', (e) => {
    e.preventDefault();
    openSearch();
});

dom.backdrop.addEventListener('click', closeSearch);
dom.closeBtn.addEventListener('click', closeSearch);

dom.input.addEventListener('input', () => {
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

function updateSelection(items) {
    items.forEach((el, i) => {
        if (i === selectedIndex) {
            el.classList.add('bg-gray-100');
            el.focus();
        } else {
            el.classList.remove('bg-gray-100');
        }
    });
}

// Global Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && dom && !dom.overlay.classList.contains('hidden')) {
        closeSearch();
    }
});
```

### `global-search.js` — what to pay attention to:

- The `buildOverlay()` function creates all the DOM elements dynamically (so you don't need to add HTML to the navbar)
- The `dom` object is initialized immediately at the bottom of the file — this is fine since the script runs after DOMContentLoaded via Vite
- Google Books helpers (`fetchGoogleBooks`, `parseBookVolume`, etc.) are duplicated from `katalog.js` — this is intentional to keep `global-search.js` self-contained
- The `performSearch` function uses `Promise.all` to fire both the local API and Google Books API in parallel
- `currentAbort` uses `AbortController` to cancel stale requests when the user types quickly

---

## Step 4: Update Navbar to Load the Script

### In `resources/views/components/navbar.blade.php`

**⚠️ Laravel 13 note:** `@vite()` works anywhere in Blade — it will emit `<link>` for CSS and `<script type="module">` for JS in the correct order. You don't need to put it in `<head>`.

Simply add `@vite(['resources/js/global-search.js'])` at the **bottom** of `navbar.blade.php` (after the closing `</nav>` tag, before any inline scripts) — use the array syntax to match the existing project style:

```blade
    </nav>
    @vite(['resources/js/global-search.js'])
    {{-- the rest of the navbar component (mobile menu, inline scripts) --}}
</div>
```

This is much simpler than modifying every page that includes the navbar. Since `@vite` is idempotent (calling it multiple times for the same file only emits it once), there's no risk of duplication even if a page also lists `global-search.js` in its own `@vite` call.

**⚠️ laravel-vite-plugin v3 note:** `@vite()` called from a Blade component works the same as from a full page template — the tags are emitted at the point where the component is rendered in the page.

### Add meta tags for auth + Google Books

The `google-books-key` meta tag is currently only on `dashboard.blade.php` and `katalog.blade.php`. For global search to use the API key on **every page**, add both meta tags to `navbar.blade.php` (anywhere before the inline `<script>` block):

```blade
<meta name="user-auth" content="{{ Auth::check() ? 'true' : 'false' }}">
<meta name="google-books-key" content="{{ config('services.google_books.key') }}">
```

These work even in `<body>` — browsers parse meta tags from anywhere in the document.

---

## Step 5: Handle Guest Users

The API endpoint is under the `auth` middleware, so guests can't access it. For the guest warning:

In `global-search.js`, check if the user is authenticated by looking for a meta tag or a data attribute.

### Add a meta tag to the `<head>` of pages:

In your layout or in each page's `<head>`, add:
```blade
<meta name="user-auth" content="{{ Auth::check() ? 'true' : 'false' }}">
```

### In `global-search.js`, check auth status:

At the top of the file, add:
```javascript
const IS_AUTHENTICATED = document.querySelector('meta[name="user-auth"]')?.content === 'true';
```

Then in the search handler:
```javascript
dom.input.addEventListener('input', () => {
    if (!IS_AUTHENTICATED) {
        dom.results.innerHTML = `
            <div class="text-center py-10 text-gray-400 text-sm">
                Silakan <a href="/login" class="text-[#5DA9FF] font-semibold underline">masuk</a> terlebih dahulu untuk mencari.
            </div>
        `;
        return;
    }
    // ... rest of search logic
});
```

---

## Step 6: Club Highlight (`?highlight={id}`)

In `resources/js/klub.js`, add this at the bottom of the file (after the init section):

```javascript
// ── Global search highlight ──
(function() {
    const params = new URLSearchParams(window.location.search);
    const highlightId = params.get('highlight');
    if (highlightId) {
        const club = CLUBS.find(c => c.id === Number(highlightId));
        if (club) {
            // Small delay to let the page render first
            setTimeout(() => openModal(club), 300);
        } else {
            // Club might not be in our pre-loaded data, fetch it
            getClubPayload(highlightId).then(data => {
                const c = mapClub(data);
                CLUBS.unshift(c);
                openModal(c);
            }).catch(() => {});
        }
    }
})();
```

---

## Step 7: Update Vite Config

**⚠️ Required for Laravel 13** — this project's `vite.config.js` has an explicit `input` array. New entry points must be added here.

Open `vite.config.js` and add `'resources/js/global-search.js'` to the `input` array:

```javascript
laravel({
    input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/global-search.js',  // <-- add this
        'resources/js/timeline.js',
        'resources/js/klub.js',
        'resources/js/dashboard.js',
        'resources/js/avatar-upload.js',
    ],
    refresh: true,
}),
```

Without this, Vite won't process `global-search.js` and `@vite` will emit nothing.

---

## Summary of Files to Create/Modify

| File | Action |
|------|--------|
| `routes/api.php` | Add `GET /api/search` route |
| `routes/web.php` | Add `GET /user/{id}` route |
| `resources/views/user_profile.blade.php` | **Create** — public user profile page |
| `resources/js/global-search.js` | **Create** — search overlay logic |
| `resources/views/components/navbar.blade.php` | Add `@vite('resources/js/global-search.js')` at bottom + user-auth meta tag |
| `resources/js/klub.js` | Add highlight handler |
| `vite.config.js` | Add `'resources/js/global-search.js'` to `input` array |

## Verification Checklist

- [ ] `GET /api/search?q=test` returns JSON with `users`, `clubs`, `books` when logged in
- [ ] `GET /api/search?q=test` returns 401/redirect when not logged in
- [ ] Search icon click opens overlay with auto-focused input
- [ ] Typing shows results grouped by Pengguna / Klub / Buku after debounce
- [ ] Google Books results appear alongside local DB results
- [ ] Arrow keys navigate results, Enter opens, Escape closes
- [ ] Clicking a Pengguna result goes to `/user/{id}`
- [ ] Clicking a Klub result goes to `/klub?highlight={id}` and opens the modal
- [ ] Clicking a Buku result goes to `/detail-buku/{id}`
- [ ] Guests see "Silakan masuk terlebih dahulu" message
- [ ] `@vite` includes `global-search.js` on every page with navbar
