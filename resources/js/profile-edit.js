document.addEventListener('DOMContentLoaded', () => {
    const csrfToken        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const GOOGLE_BOOKS_API = 'https://www.googleapis.com/books/v1/volumes';
    const GOOGLE_BOOKS_KEY = document.querySelector('meta[name="google-books-key"]')?.content || '';

    // ─────────────────────────────────────────────
    // TOAST
    // ─────────────────────────────────────────────
    function showToast(msg) {
        let el = document.getElementById('toast-msg');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toast-msg';
            el.className = 'fixed bottom-24 left-1/2 -translate-x-1/2 z-[9999] bg-[#444] text-white text-sm font-medium px-5 py-3 rounded-full transition-all duration-300 opacity-0 translate-y-2';
            document.body.appendChild(el);
        }
        el.textContent = msg;
        requestAnimationFrame(() => {
            el.classList.remove('opacity-0', 'translate-y-2');
            el.classList.add('opacity-100', 'translate-y-0');
        });
        setTimeout(() => {
            el.classList.add('opacity-0', 'translate-y-2');
            el.classList.remove('opacity-100', 'translate-y-0');
        }, 2500);
    }

    // ─────────────────────────────────────────────
    // FILTER RIWAYAT (existing list)
    // ─────────────────────────────────────────────
    const riwayatSearchInput  = document.getElementById('riwayat-search-input');
    const riwayatSearchClear  = document.getElementById('riwayat-search-clear');
    const riwayatFilterStatus = document.getElementById('riwayat-filter-status');
    const riwayatResultCount  = document.getElementById('riwayat-result-count');
    const riwayatEmpty        = document.getElementById('riwayat-empty');

    function applyRiwayatFilter() {
        const query  = riwayatSearchInput?.value.toLowerCase().trim() ?? '';
        const status = riwayatFilterStatus?.value ?? '';

        if (riwayatSearchClear) riwayatSearchClear.classList.toggle('hidden', !query);

        const allItems = document.querySelectorAll('#reading-books-list > [data-book-id]');
        let visibleCount = 0;

        allItems.forEach(item => {
            const judul      = item.dataset.judul  || '';
            const penulis    = item.dataset.penulis || '';
            const itemStatus = item.dataset.status  || '';
            const matchSearch = !query || judul.includes(query) || penulis.includes(query);
            const matchStatus = !status || itemStatus === status;
            const isVisible   = matchSearch && matchStatus;
            item.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        if (riwayatResultCount) riwayatResultCount.textContent = visibleCount;
        if (riwayatEmpty) {
            riwayatEmpty.classList.toggle('hidden', visibleCount > 0 || allItems.length === 0);
        }
    }

    let riwayatDebounce = null;
    riwayatSearchInput?.addEventListener('input', () => {
        clearTimeout(riwayatDebounce);
        riwayatDebounce = setTimeout(applyRiwayatFilter, 300);
    });
    riwayatSearchClear?.addEventListener('click', () => {
        riwayatSearchInput.value = '';
        applyRiwayatFilter();
        riwayatSearchInput.focus();
    });
    riwayatFilterStatus?.addEventListener('change', applyRiwayatFilter);
    applyRiwayatFilter();

    // ─────────────────────────────────────────────
    // BOOK SEARCH (Google Books API) → ADD TO RIWAYAT
    // ─────────────────────────────────────────────
    const bookApiSearch   = document.getElementById('book-api-search');
    const bookDropdown    = document.getElementById('book-search-dropdown');
    const bookSpinner     = document.getElementById('book-search-spinner');
    const selectedPreview = document.getElementById('selected-book-preview');
    const selectedCover   = document.getElementById('selected-cover');
    const selectedTitle   = document.getElementById('selected-title');
    const selectedAuthor  = document.getElementById('selected-author');
    const selectedClear   = document.getElementById('selected-clear');
    const addStatusSelect = document.getElementById('add-reading-status');
    const addBtn          = document.getElementById('add-reading-btn');

    let selectedBook = null;
    let apiDebounce  = null;

    function fixCoverUrl(url) {
        if (!url) return '';
        return url
            .replace(/^http:\/\//i, 'https://')
            .replace('zoom=1', 'zoom=2')
            .replace('&edge=curl', '');
    }

    async function searchBooks(query) {
        const seen    = new Set();
        const results = [];

        try {
            const res  = await fetch(`/api/books/autocomplete?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });
            if (res.ok) {
                const dbBooks = await res.json();
                dbBooks.forEach(b => {
                    const key = b.judul.toLowerCase().trim();
                    if (!seen.has(key)) {
                        seen.add(key);
                        results.push({
                            db_id   : b.id,
                            judul   : b.judul,
                            penulis : b.penulis || '',
                            cover   : b.cover_url || '',
                            source  : 'db',
                        });
                    }
                });
            }
        } catch (e) { /* DB gagal, lanjut ke API */ }

        const need = 8 - results.length;
        if (need > 0) {
            try {
                const keyParam = GOOGLE_BOOKS_KEY ? `&key=${GOOGLE_BOOKS_KEY}` : '';
                const url = `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&maxResults=${need + 4}&printType=books&orderBy=relevance${keyParam}`;
                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    (data.items || []).forEach(v => {
                        if (results.length >= 8) return;
                        const info = v.volumeInfo || {};
                        const judul = info.title || '';
                        const key   = judul.toLowerCase().trim();
                        if (judul && !seen.has(key)) {
                            seen.add(key);
                            results.push({
                                google_id: v.id,
                                judul    : judul,
                                penulis  : (info.authors || []).join(', '),
                                cover    : info.imageLinks
                                    ? fixCoverUrl(info.imageLinks.thumbnail || info.imageLinks.smallThumbnail || '')
                                    : '',
                                source   : 'google',
                            });
                        }
                    });
                }
            } catch (e) { /* Google API gagal */ }
        }

        return results;
    }

    function renderDropdown(books) {
        if (!books.length) {
            bookDropdown.innerHTML = `
                <div class="px-4 py-6 text-center text-sm text-gray-400">
                    Buku tidak ditemukan. Coba kata kunci lain.
                </div>`;
        } else {
            bookDropdown.innerHTML = books.map((b, i) => `
                <div class="book-result-item" data-idx="${i}">
                    ${b.cover
                        ? `<img src="${b.cover}" alt="${b.judul}"
                               class="w-8 h-11 object-cover rounded flex-shrink-0"
                               onerror="this.style.display='none'">`
                        : `<div class="w-8 h-11 rounded bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF]
                                       flex-shrink-0 flex items-center justify-center text-xs font-bold text-white/70">
                               ${(b.judul || '?').charAt(0).toUpperCase()}
                           </div>`
                    }
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <p class="text-sm font-semibold text-[#333] truncate">${b.judul}</p>
                            ${b.source === 'db'
                                ? `<span class="flex-shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#FFDDAF] text-[#444]">Katalog</span>`
                                : ''}
                        </div>
                        <p class="text-xs text-gray-400 truncate">${b.penulis || 'Penulis tidak diketahui'}</p>
                    </div>
                </div>
            `).join('');

            bookDropdown.querySelectorAll('.book-result-item').forEach(item => {
                item.addEventListener('mousedown', e => {
                    e.preventDefault();
                    selectBook(books[parseInt(item.dataset.idx)]);
                });
            });
        }
        bookDropdown.classList.remove('hidden');
    }

    function selectBook(book) {
        selectedBook = book;
        bookDropdown.classList.add('hidden');
        bookApiSearch.value = '';

        if (book.cover) {
            selectedCover.innerHTML = `<img src="${book.cover}" class="w-full h-full object-cover"
                                            onerror="this.remove()">`;
        } else {
            selectedCover.innerHTML = '';
            selectedCover.textContent = (book.judul || '?').charAt(0).toUpperCase();
        }
        selectedTitle.textContent  = book.judul;
        selectedAuthor.textContent = book.penulis || 'Penulis tidak diketahui';
        selectedPreview.classList.add('show');

        addBtn.removeAttribute('disabled');
        addBtn.textContent = 'Tambah';
        addBtn.className = 'px-4 py-2 bg-[#FFDDAF] border-2 border-[#444] rounded-full text-sm font-bold text-[#444] hover:bg-[#ffcf90] transition-all duration-200 cursor-pointer';
    }

    function clearSelection() {
        selectedBook = null;
        selectedPreview.classList.remove('show');
        addBtn.setAttribute('disabled', '');
        addBtn.textContent = 'Tambah';
        addBtn.className = 'px-4 py-2 bg-gray-200 border-2 border-gray-300 rounded-full text-sm font-bold text-gray-400 cursor-not-allowed transition-all duration-200';
    }

    bookApiSearch?.addEventListener('input', () => {
        clearTimeout(apiDebounce);
        const q = bookApiSearch.value.trim();
        if (q.length < 2) {
            bookDropdown.classList.add('hidden');
            return;
        }
        bookSpinner?.classList.remove('hidden');
        apiDebounce = setTimeout(async () => {
            try {
                const books = await searchBooks(q);
                renderDropdown(books);
            } catch (err) {
                bookDropdown.innerHTML = `
                    <div class="px-4 py-4 text-center text-sm text-red-400">
                        Gagal memuat hasil. Periksa koneksi internet.
                    </div>`;
                bookDropdown.classList.remove('hidden');
            } finally {
                bookSpinner?.classList.add('hidden');
            }
        }, 400);
    });

    bookApiSearch?.addEventListener('blur', () => {
        setTimeout(() => bookDropdown.classList.add('hidden'), 200);
    });
    bookApiSearch?.addEventListener('focus', () => {
        if (!bookDropdown.classList.contains('hidden') || bookApiSearch.value.trim().length >= 2) {
            if (bookDropdown.innerHTML.trim()) bookDropdown.classList.remove('hidden');
        }
    });

    selectedClear?.addEventListener('click', clearSelection);

    addBtn?.addEventListener('click', async () => {
        if (!selectedBook) {
            showToast('Pilih buku dulu dari hasil pencarian.');
            return;
        }

        addBtn.setAttribute('disabled', '');
        addBtn.textContent = 'Menyimpan...';

        const payload = {
            judul         : selectedBook.judul,
            penulis       : selectedBook.penulis,
            cover_url     : selectedBook.cover || null,
            reading_status: addStatusSelect.value,
        };

        try {
            const resp = await fetch('/profile/reading-books', {
                method : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept'      : 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });
            const result = await resp.json();
            if (!resp.ok) throw new Error(result.message || result.errors ? JSON.stringify(result.errors) : 'Gagal menyimpan');
            location.reload();
        } catch (err) {
            showToast('Error: ' + err.message);
            addBtn.removeAttribute('disabled');
            addBtn.textContent = 'Tambah';
        }
    });

    // ─────────────────────────────────────────────
    // CHANGE STATUS
    // ─────────────────────────────────────────────
    document.querySelectorAll('[data-change-status]').forEach(sel => {
        sel.addEventListener('change', async () => {
            try {
                const card = sel.closest('[data-book-id]');
                const resp = await fetch('/profile/reading-books/' + card.dataset.bookId, {
                    method : 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept'      : 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ reading_status: sel.value }),
                });
                const result = await resp.json();
                if (!resp.ok) throw new Error(result.message || 'Gagal');
                if (card) card.dataset.status = sel.value;
                applyRiwayatFilter();
                showToast('Status diperbarui.');
            } catch (err) { showToast(err.message); }
        });
    });

    // ─────────────────────────────────────────────
    // DELETE BOOK
    // ─────────────────────────────────────────────
    document.querySelectorAll('[data-delete-book]').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Hapus buku dari riwayat baca?')) return;
            try {
                const resp = await fetch('/profile/reading-books/' + btn.closest('[data-book-id]').dataset.bookId, {
                    method : 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                });
                const result = await resp.json();
                if (!resp.ok) throw new Error(result.message || 'Gagal');
                btn.closest('[data-book-id]').remove();
                applyRiwayatFilter();
                showToast('Buku berhasil dihapus.');
            } catch (err) { showToast(err.message); }
        });
    });
});
